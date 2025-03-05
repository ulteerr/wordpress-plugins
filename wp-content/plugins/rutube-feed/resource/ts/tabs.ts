import { EditorView } from "codemirror";

export function setupTabs(): void {
  const tabs =
    document.querySelectorAll<HTMLAnchorElement>(".rutube-tabs-nav a");
  const contents = document.querySelectorAll<HTMLDivElement>(".tab-content");

  tabs.forEach((tab) => {
    tab.addEventListener("click", function (e) {
      e.preventDefault();

      tabs.forEach((t) => t.classList.remove("active"));
      contents.forEach((c) => c.classList.remove("active"));

      this.classList.add("active");
      const activeTabContent = document.querySelector<HTMLDivElement>(
        this.getAttribute("href")!
      );
      activeTabContent?.classList.add("active");

      const textareas = activeTabContent?.querySelectorAll(".editor-template");
      if (textareas) {
        textareas.forEach((textarea) => {
          if (textarea && (textarea as any)._codeMirrorInstance) {
            setTimeout(() => {
              const editor = (textarea as any)
                ._codeMirrorInstance as EditorView;
              editor.requestMeasure();
            }, 50);
          }
        });
      }
    });
  });
}
