class ChordDrawer {
    constructor(canvas, chordData) {
        this.canvas = canvas;
        this.ctx = canvas.getContext('2d');
        this.chord = chordData;
        const detectedStrings = this.chord.length;

        this.config = {
            padding: 40,
            width: 130, 
            height: 180, 
            numStrings: detectedStrings,
            numFrets: 5,
            lineColor: '#2d3436',
            dotColor: '#2d3436',
            dotRadius: 10
        };

        this.draw();
    }

    draw() {
        const frets = this.chord.filter(f => f > 0);
        const minFret = frets.length > 0 ? Math.min(...frets) : 0;
        const maxFret = frets.length > 0 ? Math.max(...frets) : 0;

        let baseFret = 1;
        if (maxFret > 5) {
            baseFret = minFret; 
        }

        this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);

        this.drawGrid(baseFret);

        this.drawDots(baseFret);
    }

    drawGrid(baseFret) {
        const { padding, width, height, numStrings, numFrets } = this.config;
        const stringSpacing = width / (numStrings - 1);
        const fretSpacing = height / numFrets;
        const ctx = this.ctx;

        ctx.strokeStyle = this.config.lineColor;
        ctx.lineWidth = 1;
        ctx.beginPath();

        for (let i = 0; i <= numFrets; i++) {
            const y = padding + (i * fretSpacing);
            ctx.moveTo(padding, y);
            ctx.lineTo(padding + width, y);
        }

        for (let i = 0; i < numStrings; i++) {
            const x = padding + (i * stringSpacing);
            ctx.moveTo(x, padding);
            ctx.lineTo(x, padding + height);
        }
        ctx.stroke();

        if (baseFret === 1) {
            ctx.lineWidth = 5;
            ctx.beginPath();
            ctx.moveTo(padding, padding);
            ctx.lineTo(padding + width, padding);
            ctx.stroke();
        } else {
            ctx.font = "bold 16px Arial";
            ctx.fillStyle = this.config.lineColor;
            ctx.fillText(baseFret + "fr", 5, padding + 15);
        }
    }

    drawDots(baseFret) {
        const { padding, width, height, numStrings, numFrets } = this.config;
        const stringSpacing = width / (numStrings - 1);
        const fretSpacing = height / numFrets;
        const ctx = this.ctx;

        this.chord.forEach((fret, stringIndex) => {
            const x = padding + (stringIndex * stringSpacing);
            if (fret === 0) {
                ctx.beginPath();
                ctx.arc(x, padding - 15, 6, 0, 2 * Math.PI);
                ctx.strokeStyle = this.config.lineColor;
                ctx.lineWidth = 1;
                ctx.stroke();
            }
            else if (fret === -1) {
                ctx.font = "16px Arial";
                ctx.fillStyle = this.config.lineColor;
                ctx.fillText("X", x - 5, padding - 10);
            }
            else {
                const visibleFret = fret - baseFret + 1;
                const y = padding + (visibleFret * fretSpacing) - (fretSpacing / 2);
                
                ctx.beginPath();
                ctx.arc(x, y, this.config.dotRadius, 0, 2 * Math.PI);
                ctx.fillStyle = this.config.dotColor;
                ctx.fill();
            }
        });
    }
}