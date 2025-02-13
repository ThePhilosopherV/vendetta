import socket
import ipaddress
import argparse
from concurrent.futures import ThreadPoolExecutor

def scan_port(ip, port, timeout=1):
    """Scans a single port on a given IP."""
    try:
        with socket.socket(socket.AF_INET, socket.SOCK_STREAM) as s:
            s.settimeout(timeout)
            if s.connect_ex((ip, port)) == 0:
                return port  # Return port if it's open
    except:
        pass
    return None

def scan_host(ip, ports, max_threads=10):
    """Scans all specified ports on a single IP using multithreading."""
    open_ports = []
    with ThreadPoolExecutor(max_threads) as executor:
        results = executor.map(lambda port: scan_port(ip, port), ports)
    
    for port, status in zip(ports, results):
        if status:
            open_ports.append(port)
    
    return open_ports

def scan_network(network, ports, max_threads=50):
    """Scans all IPs in a given CIDR network for open ports using multithreading."""
    network = ipaddress.ip_network(network, strict=False)  # Get all IPs in range
    results = {}

    # print(f"🚀 Fast Scanning Network: {network} on Ports: {ports}...\n")

    with ThreadPoolExecutor(max_threads) as executor:
        future_to_ip = {executor.submit(scan_host, str(ip), ports): ip for ip in network.hosts()}

        for future in future_to_ip:
            ip = future_to_ip[future]
            open_ports = future.result()
            if open_ports:
                results[str(ip)] = open_ports
                # print(f"[+] {ip} → Open Ports: {open_ports}")

    return results

if __name__ == "__main__":
    parser = argparse.ArgumentParser(description="Fast Network Port Scanner (Multithreaded)")
    parser.add_argument("network", help="Target network in CIDR format (e.g., 192.168.1.0/24)")
    parser.add_argument("ports", help="Comma-separated list of ports (e.g., 22,80,443)")
    args = parser.parse_args()

    # Convert ports from string to a list of integers
    port_list = [int(port.strip()) for port in args.ports.split(",")]

    # Run the optimized network scan
    results = scan_network(args.network, port_list)

    # print("\nScan completed.")
    import json

# Assuming 'results' is a dictionary with host: open_ports as key-value pairs
if results:
    # Prepare the data as a dictionary for JSON output
    json_output = {host: open_ports for host, open_ports in results.items()}
    print(json.dumps(json_output, indent=4))  # Convert to JSON format and print
else:
    # Output a JSON with no open ports
    print(json.dumps({"message": "❌ No open ports found."}, indent=4))

