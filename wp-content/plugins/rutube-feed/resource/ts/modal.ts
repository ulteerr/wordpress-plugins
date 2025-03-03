import { toggleDisplay } from "./utils";

export function setupModal(): void {
  const modal = document.getElementById("rutube-modal") as HTMLElement;
  const iframe = document.getElementById("rutube-iframe") as HTMLIFrameElement;
  const closeBtn = document.querySelector(".modal-close");

  document.addEventListener("click", (event: Event) => {
    const target = event.target as HTMLElement;

    if (target.classList.contains("play-button")) {
      event.preventDefault();
      const videoSrc = target.getAttribute("data-src");
      if (videoSrc) {
        iframe.src = videoSrc;
        toggleDisplay(modal, true, "flex");
      }
    }
    if (target === modal || target === closeBtn) {
      toggleDisplay(modal, false);
      iframe.src = "";
    }
  });
}
