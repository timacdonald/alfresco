import { docsearch } from "meilisearch-docsearch";
import "meilisearch-docsearch/css";
import { computePosition, flip, offset, shift } from "@floating-ui/dom";

docsearch({
  container: "#docsearch",
  host: import.meta.env.VITE_MEILISEARCH_HOST_URL,
  apiKey: import.meta.env.VITE_MEILISEARCH_API_KEY,
  indexUid: "docs",
  hotKeys: '/'
})

const updateTooltip = (tooltip, button) => computePosition(button, tooltip, {
    placement: 'top',
    middleware: [
        flip(),
        shift({ padding: 6 }),
        offset(6)
    ],
}).then(({x, y}) => {
  Object.assign(tooltip.style, {
    left: `${x}px`,
    top: `${y}px`,
  });
});

const showTooltip = (tooltip, button) => {
    tooltip.style.display = 'block'
    updateTooltip(tooltip, button);
    tooltip.classList.add('active')
}

const hideTooltip = (tooltip) => {
    tooltip.classList.remove('active')

    window.setTimeout(() => {
        tooltip.style.display = 'none'
    }, 200)
}

[
  ['mouseenter', showTooltip],
  ['mouseleave', hideTooltip],
  ['focus', showTooltip],
  ['blur', hideTooltip],
].forEach(([event, listener]) => {
    document.querySelectorAll('button[tooltip-target]').forEach((button) => {
        const tooltip = document.getElementById(button.getAttribute('tooltip-target'))

        button.addEventListener(event, () => listener(tooltip, button));
    })
});

// const prefetched = new Set

// const prefetch = href => {
//     if (prefetched.has(href)) {
//         return
//     }

//     prefetched.add(href)

//     const link = document.createElement('link')
//     link.href = href
//     link.rel = 'prefetch'
//     link.as = 'document'
//     link.fetchPriority = 'low'

//     document.head.appendChild(link)
// }

// const anchors = () => [
//     ...document.querySelectorAll('a[href][rel~="prefetch"]')
// ].filter(el => {
//     try {
//         const url = new URL(el.href)

//         return window.location.origin === url.origin && window.location.pathname !== url.pathname
//     } catch {
//         return false
//     }
// })

// const observer = new IntersectionObserver((entries, observer) => {
//     entries.forEach(entry => {
//         if (entry.isIntersecting) {
//             observer.unobserve(entry.target)
//             prefetch(entry.target.href)
//         }
//     })
// }, { threshold: 1.0 });

// // requestIdleCallback(() => {
// setTimeout(() => {
//     if (window.navigator?.connection?.saveData === true) {
//         return;
//     }

//     if (/(2|3)g/.test(window.navigator?.connection?.effectiveType ?? '')) {
//         return;
//     }

//     anchors().forEach(el => observer.observe(el))
// })
