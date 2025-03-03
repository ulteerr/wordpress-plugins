export function handleError(img: HTMLImageElement): void {
  hidePreloader(img);
  img.src = "https://via.placeholder.com/320x180?text=No+Image";
}
export function hidePreloader(img: HTMLImageElement): void {
  toggleDisplay(img, true);

  const sibling = img.previousElementSibling;
  if (sibling instanceof HTMLElement) {
    toggleDisplay(sibling, false);
  }

  const block = img.closest(".video-thumbnail");
  const playButton = block?.querySelector(".play-button");
  if (playButton instanceof HTMLElement) {
    toggleDisplay(playButton, true);
  }
}
export function toggleDisplay(
  element: HTMLElement,
  show: boolean,
  display: string = "block"
) {
  if (element) element.style.display = show ? display : "none";
}
