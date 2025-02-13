<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>vendetta</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #0f0f0f; color: #f0f0f0; text-align: center; padding: 50px; }
        h2 { font-size: 2.5rem; margin-bottom: 20px; }
        select, input, button { padding: 12px; margin: 10px; width: 300px; border: 2px solid #333; border-radius: 8px; background-color: #1e1e1e; color: #f0f0f0; }
        select:focus, input:focus, button:focus { outline: none; border-color: #ff7e5f; }
        label { display: block; margin-top: 20px; font-size: 1.2rem; color: #ff7e5f; }
        button { background: linear-gradient(135deg, #ff7e5f, #feb47b); transition: all 0.3s ease-in-out; }
        button:hover { transform: scale(1.1); background: linear-gradient(135deg, #feb47b, #ff7e5f); }
        .result { margin-top: 30px; padding: 20px; border-radius: 10px; background-color: #1e1e1e; box-shadow: 0 4px 12px rgba(0,0,0,0.7); }
        .entry { margin-bottom: 15px; padding: 10px; border-bottom: 1px solid #333; }
        .ip { font-size: 1.3rem; color: #00c6ff; }
        .port { color: #00ffcc; margin: 5px; display: inline-block; text-decoration: none; }
        .select-container { display: inline-block; text-align: center; }

        /* Custom checkbox style */
        input[type="checkbox"] {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            width: 20px;
            height: 20px;
            border: 2px solid #ff7e5f;
            border-radius: 4px;
            position: relative;
            background-color: #1e1e1e;
            cursor: pointer;
        }

        input[type="checkbox"]:checked::before {
            content: '✔';
            position: absolute;
            top: 1px;
            left: 3px;
            color: #ff7e5f;
            font-size: 16px;
        }

        input[type="checkbox"]:hover {
            background-color: #333;
        }

        /* Align checkbox and label in one line */
        .checkbox-container {
            display: inline-flex;
            align-items: center;
        }
        #manualCheck + label { margin-left: 10px; }

        #manualLabel {
    vertical-align: middle;
    margin-top: -1px; /* Adjust this value to control the vertical position */
}
.corner-image {
    position: absolute;
    top: 10px; /* Adjust as needed */
    width: 100px; /* Adjust size as needed */
    height: auto;
}

#leftImage {
    left: 10px; /* Position image at the top-left corner */
}

#rightImage {
    right: 10px; /* Position image at the top-right corner */
}
        

    </style>
</head>
<body>
    <h2>vendetta</h2>
    <img src="left.jpg" id="leftImage" class="corner-image" alt="Left Image">
    <img src="right.jpg" id="rightImage" class="corner-image" alt="Right Image">

    <div class="select-container">
        <label for="country">Select a country</label>
        <select id="country" onchange="loadNetworks()"></select><br>
    </div>
    
    <label for="network">Select a network or input manually</label><br>
    <div class="checkbox-container">
        <input type="checkbox" id="manualCheck" onclick="toggleNetworkInput()"> 
        <label id="manualLabel" for="manualCheck">Enter network manually</label>
    </div><br><br>

    <div id="networkSelection" class="select-container">
        <select id="network" disabled><option value="">Select a network</option></select><br>
    </div>
    <div id="manualNetworkDiv" style="display:none; text-align:center;">
        <input type="text" id="manualNetwork" placeholder="Enter network manually (e.g., 192.168.1.0/24)" style="width: 300px; margin-top: 10px;"><br>
    </div>

    <label for="ports">Enter ports</label>
    <input type="text" id="ports" placeholder="e.g., 22, 80, 443"><br>

    <button onclick="scanNetwork()">Scan</button>

    <div id="result" class="result"></div>

    <script>
        window.onload = function() {
            fetch('/getCountries.php?list=true')
                .then(response => response.json())
                .then(countries => {
                    const countrySelect = document.getElementById('country');
                    countrySelect.innerHTML = '<option value="">Select a country</option>';
                    countries.forEach(country => {
                        const option = document.createElement('option');
                        option.value = country.code;
                        option.textContent = country.name;
                        countrySelect.appendChild(option);
                    });
                });
        };

        function loadNetworks() {
            const country = document.getElementById('country').value;
            if (!country || document.getElementById('manualCheck').checked) return;
            fetch(`/getCountries.php?networks=true&country=${country}`)
                .then(response => response.json())
                .then(networks => {
                    const networkSelect = document.getElementById('network');
                    networkSelect.innerHTML = '<option value="">Select a Network</option>';
                    networks.forEach(net => {
                        const option = document.createElement('option');
                        option.value = net;
                        option.textContent = net;
                        networkSelect.appendChild(option);
                    });
                    networkSelect.disabled = false;
                });
        }

        function toggleNetworkInput() {
    const isChecked = document.getElementById('manualCheck').checked;
    const networkSelection = document.getElementById('networkSelection');
    const manualNetworkDiv = document.getElementById('manualNetworkDiv');
    const countrySelect = document.getElementById('country');
    const countryLabel = document.querySelector('label[for="country"]'); // Select the "Select a country" label

    if (isChecked) {
        // Hide country label, country and network selection, show manual network input
        countryLabel.style.display = 'none';
        networkSelection.style.display = 'none';
        countrySelect.style.display = 'none';
        manualNetworkDiv.style.display = 'block';
    } else {
        // Show country label, country and network selection, hide manual network input
        countryLabel.style.display = 'block';
        networkSelection.style.display = 'block';
        countrySelect.style.display = 'block';
        manualNetworkDiv.style.display = 'none';
        loadNetworks(); // Reload networks when checkbox is unchecked
    }
}


        function scanNetwork() {
            let network = document.getElementById("network").value;
            if (!network) {
                network = document.getElementById("manualNetwork").value;
            }
            const ports = document.getElementById("ports").value;
            if (!network || !ports) { alert("Please select a network or input one manually, and enter ports."); return; }
            document.getElementById("result").innerHTML = '';
            fetch('/scan.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ address: network, ports }) })
                .then(response => response.json())
                .then(data => {
                    let resultHTML = "<h3>Scan Results:</h3>";
                    for (let ip in data) {
                        resultHTML += `<div class='entry'><div class='ip'>${ip}</div>`;
                        data[ip].forEach(port => { resultHTML += `<a href='http://${ip}:${port}' class='port' target='_blank'>${port}</a>`; });
                        resultHTML += `</div>`;
                    }
                    document.getElementById("result").innerHTML = resultHTML;
                })
                .catch(error => { console.error("Error:", error); document.getElementById("result").innerText = "Error scanning."; });
        }
    </script>
</body>
</html>
