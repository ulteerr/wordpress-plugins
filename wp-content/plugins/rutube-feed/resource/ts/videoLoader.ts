import { RutubeAdminParams } from "./interface/RutubeAdminParams.interface";
import { hidePreloader, handleError } from "./utils";

export async function loadVideos(
  pageNumber: number,
  rutubeAdminParams: RutubeAdminParams,
  videoBlock: HTMLElement | null,
  loadingItems: HTMLElement | null,
  channel: string | undefined,
  limit: string | undefined,
  append: boolean = false
): Promise<void> {
  if (!videoBlock || !loadingItems) return;
  const formData = new FormData();
  formData.append("action", "load_admin_videos");
  formData.append("post_id", rutubeAdminParams.post_id);
  formData.append("channel", channel || "");
  formData.append("limit", limit || "");
  formData.append("page", pageNumber.toString());

  try {
    const response = await fetch(rutubeAdminParams.ajax_url, {
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
        rutubeAdminParams,
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

function showLoadMoreButton(
  rutubeAdminParams: RutubeAdminParams,
  hasMore: boolean,
  count: number,
  page: number,
  videoBlock: HTMLElement,
  loadingItems: HTMLElement,
  channel?: string,
  limit?: string
): void {
  loadingItems.innerHTML = "";
  if (hasMore && count) {
    const nextButton = document.createElement("button");
    nextButton.textContent = "Показать ещё";
    nextButton.classList.add("next-page");
    nextButton.addEventListener("click", (e) => {
      e.preventDefault();
      loadVideos(
        page + 1,
        rutubeAdminParams,
        videoBlock,
        loadingItems,
        channel,
        limit,
        true
      );
    });
    loadingItems.appendChild(nextButton);
  }
}
