document.addEventListener("DOMContentLoaded", function () {
    function checkBackgroundImage() {
        let testImg = new Image();
        testImg.src = "/luci-static/resources/background/bg1.jpg";

        testImg.onload = function () {
            applyCSS(true);
        };

        testImg.onerror = function () {
            applyCSS(false);
        };
    }

    function applyCSS(imageExists) {
        let styleTag = document.querySelector("#dynamic-style");
        if (!styleTag) {
            styleTag = document.createElement("style");
            styleTag.id = "dynamic-style";
            document.head.appendChild(styleTag);
        }

        if (imageExists) {
            styleTag.innerHTML = `
                body {
                    background: url('/luci-static/resources/background/bg1.jpg') no-repeat center center fixed !important;
                    background-size: cover !important;
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

    checkBackgroundImage();
    setInterval(checkBackgroundImage, 1000);
});