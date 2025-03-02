import { hidePreloader } from "./hidePreloader";
export function handleError(img) {
	hidePreloader(img);
	img.src = "https://via.placeholder.com/320x180?text=No+Image";
}
