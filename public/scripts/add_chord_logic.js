document.addEventListener("DOMContentLoaded", function() {
    const instrumentSelect = document.getElementById('instrumentSelect');
    const tuningSelect = document.getElementById('tuningSelect');
    const stringsContainer = document.getElementById('stringsContainer');
    const stringCountInput = document.getElementById('stringCountInput');

    // 1. Funkcja generująca inputy dla strun
    function generateStringInputs(count) {
        stringsContainer.innerHTML = ''; // Wyczyść stare
        stringCountInput.value = count;  // Zaktualizuj hidden input dla PHP

        for (let i = count; i >= 1; i--) {
            const wrapper = document.createElement('div');
            wrapper.className = 'string-input';
            
            const label = document.createElement('label');
            label.innerText = `Struna ${i}`;
            
            const select = document.createElement('select');
            select.name = `string${i}`;
            
            // Opcja X (tłumiona)
            const optX = document.createElement('option');
            optX.value = "-1";
            optX.text = "X";
            select.appendChild(optX);

            // Opcja 0 (pusta) - domyślna
            const opt0 = document.createElement('option');
            opt0.value = "0";
            opt0.text = "0";
            opt0.selected = true;
            select.appendChild(opt0);

            // Progi 1-24
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

    // 2. Funkcja pobierająca strojenia z API
    function fetchTunings(instrumentId) {
        tuningSelect.innerHTML = '<option>Ładowanie...</option>';
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
            data.forEach(tuning => {
                const opt = document.createElement('option');
                opt.value = tuning.id;
                opt.text = tuning.tuning;
                tuningSelect.appendChild(opt);
            });
            tuningSelect.disabled = false;
        })
        .catch(error => {
            console.error('Error:', error);
            tuningSelect.innerHTML = '<option>Błąd pobierania</option>';
        });
    }

    // 3. Nasłuchiwanie zmian
    instrumentSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const instrumentId = this.value;
        const stringsCount = selectedOption.getAttribute('data-strings');

        // Generuj inputy
        generateStringInputs(stringsCount);
        // Pobierz strojenia
        fetchTunings(instrumentId);
    });

    // 4. Inicjalizacja na starcie (dla domyślnego instrumentu, zazwyczaj Gitara)
    if (instrumentSelect.value) {
        const selectedOption = instrumentSelect.options[instrumentSelect.selectedIndex];
        generateStringInputs(selectedOption.getAttribute('data-strings'));
        fetchTunings(instrumentSelect.value);
    }
});