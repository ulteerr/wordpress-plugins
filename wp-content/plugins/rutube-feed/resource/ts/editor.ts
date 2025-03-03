import { EditorView, basicSetup } from "codemirror";
import { html } from "@codemirror/lang-html";
import { css } from "@codemirror/lang-css";
import { oneDark } from "@codemirror/theme-one-dark";

// Функция для создания редактора
function createEditor(textarea: HTMLTextAreaElement) {
    const mode = textarea.dataset.editorMode ?? "html";
    let languageExtension;

    switch (mode) {
        case "htmlmixed":
            languageExtension = html();
            break;
        case "css":
            languageExtension = css();
            break;
        default:
            languageExtension = html();
    }

    const editor = new EditorView({
        doc: textarea.value,
        extensions: [
            basicSetup,
            languageExtension,
            oneDark,
            EditorView.lineWrapping,
            EditorView.updateListener.of((update) => {
                if (update.docChanged) {
                    textarea.value = editor.state.doc.toString();
                }
            })
        ],
        parent: textarea.parentElement as HTMLElement,
    });

    (textarea as any)._codeMirrorInstance = editor;
    return editor;
}
