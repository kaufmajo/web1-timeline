import { date_createObjectFromString } from "./module/date-helpers.js";

// ---------------------------------------------------------------------------
// Refresh website if necessary

function handleReload() {
    // init
    let pageDatetime = document.querySelector('body').dataset.pageDatetime;
    let loading_date = date_createObjectFromString(pageDatetime);
    let current_date = new Date();
    // process
    if ((current_date - loading_date) > (60 * 5 * 12 * 1000 * 3)) { // 60 * 5 * 12 * 1000 * 3 => 3 h
        window.removeEventListener('visibilitychange', handleReload, false);
        alert("Webseite wird neu geladen.");
        window.location.href = "/?reload=" + pageDatetime;
    }
}

window.addEventListener('visibilitychange', handleReload, false);

// ---------------------------------------------------------------------------
// Jump to hash if none is given

window.onload = function (event) {
    if (!window.location.hash) {
        const today = (new Date()).getFullYear() + '-'
            + ('0' + ((new Date()).getMonth() + 1)).slice(-2) + '-'
            + ('0' + (new Date()).getDate()).slice(-2);
        window.location.hash = "#anchor-" + today;
    }
}

// ---------------------------------------------------------------------------
// Run highlight

const highlightElements = document.querySelectorAll("[data-highlight='1']");

for (let i = 0, len = highlightElements.length; i < len; i++) {
    const listItem = highlightElements[i].closest("li");
    setTimeout(function () { listItem.classList.add("highlight") }, 100 * i);
    listItem.addEventListener("transitionend", () => { listItem.classList.remove("highlight") });
}

// ---------------------------------------------------------------------------
// One-time "Nudge=Stubs"

const gridCells = document.querySelectorAll("ol>li");

// Utility: sleep for async/await
function sleep(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}

function smoothScrollTo(container, target, duration) {
    return new Promise(resolve => {
        const start = container.scrollTop;
        const distance = target - start;
        const startTime = performance.now();

        function step(currentTime) {

            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);

            // Ease-in-out interpolation
            const ease = 0.5 * (1 - Math.cos(Math.PI * progress));
            container.scrollTop = start + distance * ease;

            if (progress < 1) {
                requestAnimationFrame(step);
            } else {
                resolve(); // Finish and resume async flow
            }
        }

        requestAnimationFrame(step);
    });
}

// Animate scrolling for one container
async function animateScrolling(container) {
    await smoothScrollTo(container, container.scrollHeight, 2000);
    await sleep(0);
    await smoothScrollTo(container, 0, 2000);
}

// On window load, scroll all overflowing gridCells
window.addEventListener('load', async () => {
    const tasks = [];
    for (let container of gridCells) {
        if (container.scrollHeight - container.clientHeight > 20) {
            tasks.push(animateScrolling(container));
            //await animateScrolling(container); // Wait for each to finish before next
        }
    }
    await Promise.all(tasks); // Wait for all to finish (optional)
});

// ---------------------------------------------------------------------------