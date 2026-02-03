document.addEventListener("DOMContentLoaded", function() {
    const instrumentSelect = document.getElementById('instrumentSelect');
    const tuningSelect = document.getElementById('tuningSelect');


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
            tuningSelect.innerHTML = '<option>Error loading tunings</option>';
        });
    }


    if (instrumentSelect) {
        instrumentSelect.addEventListener('change', function() {
            fetchTunings(this.value);
        });

        if (instrumentSelect.value) {
            fetchTunings(instrumentSelect.value);
        }
    }
});