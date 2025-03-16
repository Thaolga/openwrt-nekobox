document.addEventListener("DOMContentLoaded", function () {
    const bgImages = Array.from({ length: 5 }, (_, i) => `bg${i + 1}.jpg`);
    let bgIndex = Math.floor(Math.random() * bgImages.length); 
    let availableImages = [];

    function checkImageExists(image, callback) {
        let testImg = new Image();
        testImg.src = `/luci-static/resources/background/${image}`;
        testImg.onload = function () {
            callback(true);
        };
        testImg.onerror = function () {
            callback(false);
        };
    }

    function applyCSS(image) {
        let styleTag = document.querySelector("#dynamic-style");
        if (!styleTag) {
            styleTag = document.createElement("style");
            styleTag.id = "dynamic-style";
            document.head.appendChild(styleTag);
        }

        if (image) {
            styleTag.innerHTML = `
                body {
                    background: url('/luci-static/resources/background/${image}') no-repeat center center fixed !important;
                    background-size: cover !important;
                    transition: background 1s ease-in-out;
                }
                .wrapper span {
                    display: none !important;
                }
            `;
        } else {
            styleTag.innerHTML = `
                body {
                    background: #111 !important;
                    background-image: none !important;
                    background-size: auto !important;
                }
                .wrapper span {
                    display: block !important;
                }
            `;
        }
    }

    function loadInitialBackground() {
        let checkedImages = 0;

        bgImages.forEach((image, index) => {
            checkImageExists(image, function (exists) {
                checkedImages++;
                console.log(`Checking bg${index + 1}.jpg: ${exists}`);
                if (exists) {
                    availableImages.push(image);
                }

                if (checkedImages === bgImages.length) {
                    if (availableImages.length > 0) {
                        bgIndex = Math.floor(Math.random() * availableImages.length);
                        applyCSS(availableImages[bgIndex]);
                    } else {
                        applyCSS(null); 
                    }
                }
            });
        });
    }

    function switchBackground() {
        console.log("Switching background...");
        if (availableImages.length > 1) {
            let nextIndex = bgIndex;
            while (nextIndex === bgIndex) {
                nextIndex = Math.floor(Math.random() * availableImages.length);
            }
            bgIndex = nextIndex;
            applyCSS(availableImages[bgIndex]);
        }
    }

    function checkCurrentBackground() {
        console.log("Checking current background...");
        checkImageExists(availableImages[bgIndex], function (exists) {
            console.log(`Current bg${bgIndex + 1}.jpg exists: ${exists}`);
            if (!exists) {
                availableImages.splice(bgIndex, 1);
                if (availableImages.length > 0) {
                    bgIndex = Math.floor(Math.random() * availableImages.length);
                    applyCSS(availableImages[bgIndex]);
                } else {
                    applyCSS(null); 
                }
            }
        });
    }

    loadInitialBackground();
    setInterval(checkCurrentBackground, 1000);
    setInterval(switchBackground, 120000);
});