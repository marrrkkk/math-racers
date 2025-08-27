// Simple sound effects using Web Audio API
class SoundEffects {
    constructor() {
        this.audioContext = null;
        this.enabled = true;
        this.initAudioContext();
    }

    initAudioContext() {
        try {
            this.audioContext = new (window.AudioContext ||
                window.webkitAudioContext)();
        } catch (error) {
            console.warn("Web Audio API not supported:", error);
            this.enabled = false;
        }
    }

    // Play a success sound (higher pitch beep)
    playSuccess() {
        if (!this.enabled || !this.audioContext) return;

        try {
            const oscillator = this.audioContext.createOscillator();
            const gainNode = this.audioContext.createGain();

            oscillator.connect(gainNode);
            gainNode.connect(this.audioContext.destination);

            oscillator.frequency.setValueAtTime(
                800,
                this.audioContext.currentTime
            );
            oscillator.frequency.setValueAtTime(
                1000,
                this.audioContext.currentTime + 0.1
            );

            gainNode.gain.setValueAtTime(0.3, this.audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(
                0.01,
                this.audioContext.currentTime + 0.3
            );

            oscillator.start(this.audioContext.currentTime);
            oscillator.stop(this.audioContext.currentTime + 0.3);
        } catch (error) {
            console.warn("Error playing success sound:", error);
        }
    }

    // Play an error sound (lower pitch beep)
    playError() {
        if (!this.enabled || !this.audioContext) return;

        try {
            const oscillator = this.audioContext.createOscillator();
            const gainNode = this.audioContext.createGain();

            oscillator.connect(gainNode);
            gainNode.connect(this.audioContext.destination);

            oscillator.frequency.setValueAtTime(
                300,
                this.audioContext.currentTime
            );
            oscillator.frequency.setValueAtTime(
                200,
                this.audioContext.currentTime + 0.1
            );

            gainNode.gain.setValueAtTime(0.3, this.audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(
                0.01,
                this.audioContext.currentTime + 0.4
            );

            oscillator.start(this.audioContext.currentTime);
            oscillator.stop(this.audioContext.currentTime + 0.4);
        } catch (error) {
            console.warn("Error playing error sound:", error);
        }
    }

    // Play a completion sound (ascending notes)
    playCompletion() {
        if (!this.enabled || !this.audioContext) return;

        try {
            const notes = [523, 659, 784, 1047]; // C, E, G, C (one octave higher)

            notes.forEach((frequency, index) => {
                const oscillator = this.audioContext.createOscillator();
                const gainNode = this.audioContext.createGain();

                oscillator.connect(gainNode);
                gainNode.connect(this.audioContext.destination);

                oscillator.frequency.setValueAtTime(
                    frequency,
                    this.audioContext.currentTime
                );

                const startTime = this.audioContext.currentTime + index * 0.2;
                const endTime = startTime + 0.3;

                gainNode.gain.setValueAtTime(0.2, startTime);
                gainNode.gain.exponentialRampToValueAtTime(0.01, endTime);

                oscillator.start(startTime);
                oscillator.stop(endTime);
            });
        } catch (error) {
            console.warn("Error playing completion sound:", error);
        }
    }

    // Play a magical sparkle sound for celebrations
    playSparkle() {
        if (!this.enabled || !this.audioContext) return;

        try {
            const oscillator = this.audioContext.createOscillator();
            const gainNode = this.audioContext.createGain();

            oscillator.connect(gainNode);
            gainNode.connect(this.audioContext.destination);

            // Create a magical ascending tone
            oscillator.frequency.setValueAtTime(
                800,
                this.audioContext.currentTime
            );
            oscillator.frequency.exponentialRampToValueAtTime(
                1600,
                this.audioContext.currentTime + 0.3
            );

            gainNode.gain.setValueAtTime(0.1, this.audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(
                0.01,
                this.audioContext.currentTime + 0.3
            );

            oscillator.start(this.audioContext.currentTime);
            oscillator.stop(this.audioContext.currentTime + 0.3);
        } catch (error) {
            console.warn("Error playing sparkle sound:", error);
        }
    }

    // Play a cheerful button click sound
    playClick() {
        if (!this.enabled || !this.audioContext) return;

        try {
            const oscillator = this.audioContext.createOscillator();
            const gainNode = this.audioContext.createGain();

            oscillator.connect(gainNode);
            gainNode.connect(this.audioContext.destination);

            oscillator.frequency.setValueAtTime(
                600,
                this.audioContext.currentTime
            );
            oscillator.frequency.setValueAtTime(
                800,
                this.audioContext.currentTime + 0.05
            );

            gainNode.gain.setValueAtTime(0.2, this.audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(
                0.01,
                this.audioContext.currentTime + 0.1
            );

            oscillator.start(this.audioContext.currentTime);
            oscillator.stop(this.audioContext.currentTime + 0.1);
        } catch (error) {
            console.warn("Error playing click sound:", error);
        }
    }

    // Play a whoosh sound for fast movements
    playWhoosh() {
        if (!this.enabled || !this.audioContext) return;

        try {
            const oscillator = this.audioContext.createOscillator();
            const gainNode = this.audioContext.createGain();

            oscillator.connect(gainNode);
            gainNode.connect(this.audioContext.destination);

            oscillator.frequency.setValueAtTime(
                200,
                this.audioContext.currentTime
            );
            oscillator.frequency.exponentialRampToValueAtTime(
                100,
                this.audioContext.currentTime + 0.4
            );

            gainNode.gain.setValueAtTime(0.15, this.audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(
                0.01,
                this.audioContext.currentTime + 0.4
            );

            oscillator.start(this.audioContext.currentTime);
            oscillator.stop(this.audioContext.currentTime + 0.4);
        } catch (error) {
            console.warn("Error playing whoosh sound:", error);
        }
    }

    // Play a power-up sound for achievements
    playPowerUp() {
        if (!this.enabled || !this.audioContext) return;

        try {
            const frequencies = [261, 329, 392, 523]; // C, E, G, C major chord

            frequencies.forEach((frequency, index) => {
                const oscillator = this.audioContext.createOscillator();
                const gainNode = this.audioContext.createGain();

                oscillator.connect(gainNode);
                gainNode.connect(this.audioContext.destination);

                oscillator.frequency.setValueAtTime(
                    frequency,
                    this.audioContext.currentTime
                );

                const startTime = this.audioContext.currentTime + index * 0.1;
                const endTime = startTime + 0.5;

                gainNode.gain.setValueAtTime(0.1, startTime);
                gainNode.gain.exponentialRampToValueAtTime(0.01, endTime);

                oscillator.start(startTime);
                oscillator.stop(endTime);
            });
        } catch (error) {
            console.warn("Error playing power-up sound:", error);
        }
    }

    // Play a racing engine sound (for racer movement)
    playRacerMove() {
        if (!this.enabled || !this.audioContext) return;

        try {
            const oscillator = this.audioContext.createOscillator();
            const gainNode = this.audioContext.createGain();

            oscillator.connect(gainNode);
            gainNode.connect(this.audioContext.destination);

            oscillator.frequency.setValueAtTime(
                150,
                this.audioContext.currentTime
            );
            oscillator.frequency.setValueAtTime(
                250,
                this.audioContext.currentTime + 0.1
            );
            oscillator.frequency.setValueAtTime(
                200,
                this.audioContext.currentTime + 0.2
            );

            gainNode.gain.setValueAtTime(0.1, this.audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(
                0.01,
                this.audioContext.currentTime + 0.3
            );

            oscillator.start(this.audioContext.currentTime);
            oscillator.stop(this.audioContext.currentTime + 0.3);
        } catch (error) {
            console.warn("Error playing racer move sound:", error);
        }
    }

    // Enable/disable sound effects
    toggle() {
        this.enabled = !this.enabled;
        return this.enabled;
    }

    // Check if sounds are enabled
    isEnabled() {
        return this.enabled;
    }
}

// Create a singleton instance
const soundEffects = new SoundEffects();

export default soundEffects;
