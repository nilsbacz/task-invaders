import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap';
import './styles/app.css';

const STARFIELD_ID = 'starfield';

function initStarfield() {
    const canvas = document.getElementById(STARFIELD_ID);
    if (!(canvas instanceof HTMLCanvasElement)) {
        return;
    }

    const context = canvas.getContext('2d');
    if (context === null) {
        return;
    }

    const randomBuffer = new Uint32Array(1);
    const stars = [];
    const starLayers = [
        { density: 0.000045, minSize: 0.45, maxSize: 1.15, minSpeed: 0.04, maxSpeed: 0.16, minAlpha: 0.28, maxAlpha: 0.6 },
        { density: 0.000018, minSize: 1.1, maxSize: 2.05, minSpeed: 0.18, maxSpeed: 0.42, minAlpha: 0.45, maxAlpha: 0.78 },
        { density: 0.000007, minSize: 2.1, maxSize: 3.4, minSpeed: 0.48, maxSpeed: 0.86, minAlpha: 0.7, maxAlpha: 0.98 },
    ];
    let viewportWidth = 0;
    let viewportHeight = 0;
    let devicePixelRatioValue = 1;
    let animationFrameId = null;

    function randomUnit() {
        crypto.getRandomValues(randomBuffer);

        return randomBuffer[0] / 4294967295;
    }

    function interpolate(start, end, progress) {
        return start + (end - start) * progress;
    }

    function wrap(value, max) {
        return ((value % max) + max) % max;
    }

    function rebuildStars() {
        stars.length = 0;

        for (const layer of starLayers) {
            const count = Math.max(12, Math.round(viewportWidth * viewportHeight * layer.density));

            for (let index = 0; index < count; index += 1) {
                const sizeBias = randomUnit() ** 1.6;
                const size = interpolate(layer.minSize, layer.maxSize, sizeBias);
                const speedProgress = (size - layer.minSize) / (layer.maxSize - layer.minSize || 1);

                stars.push({
                    x: randomUnit() * viewportWidth,
                    y: randomUnit() * viewportHeight,
                    size,
                    speed: interpolate(layer.minSpeed, layer.maxSpeed, speedProgress),
                    alpha: interpolate(layer.minAlpha, layer.maxAlpha, randomUnit()),
                });
            }
        }
    }

    function resizeCanvas() {
        devicePixelRatioValue = window.devicePixelRatio || 1;
        viewportWidth = window.innerWidth;
        viewportHeight = window.innerHeight;

        canvas.width = Math.max(1, Math.floor(viewportWidth * devicePixelRatioValue));
        canvas.height = Math.max(1, Math.floor(viewportHeight * devicePixelRatioValue));
        canvas.style.width = `${viewportWidth}px`;
        canvas.style.height = `${viewportHeight}px`;

        context.setTransform(devicePixelRatioValue, 0, 0, devicePixelRatioValue, 0, 0);

        rebuildStars();
        render();
    }

    function render() {
        context.clearRect(0, 0, viewportWidth, viewportHeight);

        const scrollOffset = window.scrollY;

        for (const star of stars) {
            const y = wrap(star.y - scrollOffset * star.speed, viewportHeight);

            context.beginPath();
            context.fillStyle = `rgba(255, 255, 255, ${star.alpha})`;
            context.arc(star.x, y, star.size, 0, Math.PI * 2);
            context.fill();
        }

        animationFrameId = null;
    }

    function requestRender() {
        if (animationFrameId !== null) {
            return;
        }

        animationFrameId = window.requestAnimationFrame(render);
    }

    window.addEventListener('resize', resizeCanvas);
    window.addEventListener('scroll', requestRender, { passive: true });

    resizeCanvas();
}

initStarfield();
