import { showLoadMoreButton } from "./actions";
import { RutubeAdminParams } from "./interface/RutubeAdminParams.interface";
import { RutubeParams } from "./interface/RutubeParams";
import { hidePreloader, handleError } from "./utils";

export async function loadVideos(
  pageNumber: number,
  params: RutubeAdminParams | RutubeParams,
  videoBlock: HTMLElement | null,
  loadingItems: HTMLElement | null,
  channel: string | undefined,
  limit: string | undefined,
  append: boolean = false
): Promise<void> {
  if (!videoBlock || !loadingItems) return;
  const formData = new FormData();
  formData.append("action", "load_admin_videos");
  formData.append("post_id", params.post_id);
  formData.append("channel", channel || "");
  formData.append("limit", limit || "");
  formData.append("page", pageNumber.toString());

  try {
    const response = await fetch(params.ajax_url, {
      method: "POST",
      body: formData,
    });
    const data = await response.json();

    if (data.success) {
      videoBlock.innerHTML = append
        ? videoBlock.innerHTML + data.data.html
        : data.data.html;

      document.querySelectorAll(".lazy-thumbnail").forEach((img) => {
        img.addEventListener("load", () =>
          hidePreloader(img as HTMLImageElement)
        );
        img.addEventListener("error", () =>
          handleError(img as HTMLImageElement)
        );
      });

      showLoadMoreButton(
        params,
        data.data.has_more,
        data.data.count,
        pageNumber,
        videoBlock,
        loadingItems,
        channel,
        limit
      );
    }
  } catch (error) {
    console.error("Ошибка загрузки видео:", error);
  }
}
