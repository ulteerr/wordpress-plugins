import { toggleDisplay } from "./toggleDisplay";

export function hidePreloader(img) {
	toggleDisplay(img, true);
	toggleDisplay(img.previousElementSibling, false);

	const block = img.closest('.video-thumbnail');
	const playButton = block?.querySelector('.play-button');
	if (playButton) toggleDisplay(playButton, true);
}