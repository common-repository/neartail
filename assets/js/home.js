function loadVideoPromise(src) {
    return new Promise((resolve, reject) => {
        const video = document.createElement('video');
        video.preload = 'auto';
        video.autoplay = true;
        video.loop = false;
        video.muted = true;
        video.style.width = '100%';
        const source = document.createElement('source');
        source.src = src;
        source.type = 'video/webm';
        video.appendChild(source);

        video.addEventListener('canplaythrough', () => {
            resolve(video);
        }, { once: true });

        video.addEventListener('ended', function() {
            setTimeout(function() {
                video.currentTime = 0; 
                video.play();
            }, 3000); 
        });

        video.addEventListener('error', (error) => {
            reject(error);
        });
    });
}

function loadLottiePromise(src) {
    return new Promise(function(resolve, reject) {
        var anim = lottie.loadAnimation({
            container: document.getElementById('lottie-container'),
            renderer: 'svg',
            loop: false,
            autoplay: false, // Autoplay set to false initially
            path: src,
            rendererSettings: { progressiveLoad: true },
        });

        anim.addEventListener('data_ready', function() {
            resolve(anim);
        });

        anim.addEventListener('data_failed', function(error) {
            reject(error);
        });

        anim.addEventListener('complete', function () {
            // Pause the animation for 3 seconds
            setTimeout(function () {
                anim.goToAndPlay(0); // Go to the beginning and play
            }, 3000); // 3000 milliseconds = 3 seconds
        });
    });
}

function loadScript(src) {
    return new Promise(function(resolve, reject) {
        var script = document.createElement('script');
        script.src = src;
        script.onload = resolve;
        script.onerror = reject;
        document.head.appendChild(script);
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const delayPromise = new Promise((resolve) => {
        setTimeout(() => { resolve(); }, 3000);
    });

    
    const img = document.querySelector('.lazyAnimate');
    if(img && img.dataset.jsonSrc) {
        const jsonSrc = img.dataset.jsonSrc;
        // Animate video once video is ready & dom is ready after 3 sec
        Promise.all([loadLottiePromise(jsonSrc), delayPromise]).then((rs) => {
            var lottieAnimation = rs[0];
            document.querySelector('.lazyAnimate').remove();
            document.getElementById('lottie-container').style.display = 'block';
            lottieAnimation.play();
        }).catch((error) => {
            console.error('Error:', error);
        });
    }
    
});