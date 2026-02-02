document.addEventListener("DOMContentLoaded", function() {
    const instrumentSelect = document.getElementById('instrumentSelect');
    const tuningSelect = document.getElementById('tuningSelect');
    const stringsContainer = document.getElementById('stringsContainer');
    const stringCountInput = document.getElementById('stringCountInput');

    function generateStringInputs(count) {
        stringsContainer.innerHTML = ''; 
        stringCountInput.value = count;

        for (let i = count; i >= 1; i--) {
            const wrapper = document.createElement('div');
            wrapper.className = 'string-input';
            
            const label = document.createElement('label');
            label.innerText = `String ${i}`;
            
            const select = document.createElement('select');
            select.name = `string${i}`;
            
            const optX = document.createElement('option');
            optX.value = "-1";
            optX.text = "X";
            select.appendChild(optX);

            const opt0 = document.createElement('option');
            opt0.value = "0";
            opt0.text = "0";
            opt0.selected = true;
            select.appendChild(opt0);

            for (let f = 1; f <= 24; f++) {
                const opt = document.createElement('option');
                opt.value = f;
                opt.text = f;
                select.appendChild(opt);
            }

            wrapper.appendChild(label);
            wrapper.appendChild(select);
            stringsContainer.appendChild(wrapper);
        }
    }

    function fetchTunings(instrumentId) {
        tuningSelect.innerHTML = '<option>Loading...</option>';
        tuningSelect.disabled = true;

        fetch('/api-get-tunings', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ instrument_id: instrumentId })
        })
        .then(response => response.json())
        .then(data => {
            tuningSelect.innerHTML = '';
            
            if (data.length === 0) {
                 tuningSelect.innerHTML = '<option disabled>No tunings available</option>';
            } else {
                data.forEach(tuning => {
                    const opt = document.createElement('option');
                    opt.value = tuning.id;
                    opt.text = tuning.tuning;
                    tuningSelect.appendChild(opt);
                });
            }
            tuningSelect.disabled = false;
        })
        .catch(error => {
            console.error('Error:', error);
            tuningSelect.innerHTML = '<option>Error loading</option>';
        });
    }

    instrumentSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const instrumentId = this.value;
        const stringsCount = selectedOption.getAttribute('data-strings');

        generateStringInputs(stringsCount);
        fetchTunings(instrumentId);
    });

    if (instrumentSelect.value) {
        const selectedOption = instrumentSelect.options[instrumentSelect.selectedIndex];
        generateStringInputs(selectedOption.getAttribute('data-strings'));
        fetchTunings(instrumentSelect.value);
    }
});