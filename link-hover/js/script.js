document.addEventListener("DOMContentLoaded", function () {

    let tooltip = document.createElement("div");
    tooltip.className = "lpt-tooltip";
    document.body.appendChild(tooltip);

    let cache = {};

    document.querySelectorAll("a[href^='http']").forEach(link => {

        let linkHost = new URL(link.href).hostname;
    let currentHost = window.location.hostname;

    if (linkHost === currentHost) return;

        link.addEventListener("mouseenter", function () {
            let url = this.href;

        
            if (cache[url]) {
                tooltip.innerHTML = cache[url];
                tooltip.style.display = "block";
                return;
            }

            fetch(`${lpt_ajax.ajax_url}?action=lpt_fetch&url=${url}&nonce=${lpt_ajax.nonce}`)
                .then(res => res.json())
                .then(data => {

                    let html = `
                        ${data.image ? `<img src="${data.image}">` : ''}
                        <div class="lpt-content">
                            <div class="lpt-title">${data.title || ''}</div>
                            <div class="lpt-desc">${data.desc || ''}</div>
                        </div>
                    `;

                    cache[url] = html;
                    tooltip.innerHTML = html;
                    tooltip.style.display = "block";
                });
        });

        link.addEventListener("mouseleave", function () {
            tooltip.style.display = "none";
        });

        link.addEventListener("mousemove", function (e) {
            tooltip.style.top = (e.pageY - 300) + "px";
            tooltip.style.left = (e.pageX - 100) + "px";
        });

    });

});