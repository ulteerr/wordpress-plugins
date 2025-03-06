import { RutubeParams } from "./ts/interface/RutubeParams";
import { loadVideos } from "./ts/videoLoader";
declare const rutubeParams: RutubeParams;

const container = document.getElementById("rutube-video-container");
const videoBlock = container?.querySelector(
  ".rutube-video-grid"
) as HTMLElement;
const channel = container?.dataset.channel;
const limit = container?.dataset.limit;
const loadingItems = document.getElementById("rutube-loading-items");
const nextPage = loadingItems?.querySelector(".next-page");
const page = Number(container?.dataset.page) || 1;

if (nextPage) {
  nextPage.addEventListener("click", (e) => {
    e.preventDefault();
    loadVideos(
      page + 1,
      rutubeParams,
      videoBlock,
      loadingItems,
      channel,
      limit,
      true
    );
  });
}
