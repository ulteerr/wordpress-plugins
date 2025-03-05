import { basicSetup, EditorView } from "codemirror";
import { javascript } from "@codemirror/lang-javascript";
import { keymap, ViewUpdate } from "@codemirror/view";
import { html } from "@codemirror/lang-html";
import { css } from "@codemirror/lang-css";
import { oneDark } from "@codemirror/theme-one-dark";
import { indentWithTab } from "@codemirror/commands";
import { html as htmlBeautify, css as cssBeautify } from "js-beautify";

// let language = new Compartment();
function formatEditorContent(
  editor: EditorView,
  beautifyFunction?: (code: string, options: object) => string
): void {
  if (beautifyFunction) {
    const code = editor.state.doc.toString();
    const formatted = beautifyFunction(code, {
      indent_size: 4,
      wrap_line_length: 80,
      preserve_newlines: true,
    });
    editor.dispatch({
      changes: { from: 0, to: editor.state.doc.length, insert: formatted },
    });
  } else {
    console.error("js-beautify не загружен!");
  }
}

function duplicateLine(view: EditorView): boolean {
  const { state } = view;
  const { from } = state.selection.main;
  const line = state.doc.lineAt(from);
  view.dispatch({
    changes: {
      from: line.from,
      to: line.from,
      insert: state.doc.sliceString(line.from, line.to) + "\n",
    },
    selection: { anchor: from + (line.to - line.from) + 1 },
  });
  return true;
}

export function setupEditor(textarea: HTMLTextAreaElement): void {
  const mode = textarea.dataset.editorMode || "html";

  const parentElement = textarea.parentElement!;

  let modeExtensions: any[] = [];

  if (mode === "css") {
    modeExtensions = [css()];
  } else if (mode === "js") {
    modeExtensions = [javascript()];
  } else if (mode === "html") {
    modeExtensions = [html()];
  }

  const editor = new EditorView({
    extensions: [
      basicSetup,
      oneDark,
      keymap.of([indentWithTab, { key: "Ctrl-d", run: duplicateLine }]),
      ...modeExtensions,
      EditorView.updateListener.of((update: ViewUpdate) => {
        textarea.value = update.state.doc.toString();
      }),
    ],
    parent: textarea.parentElement!,
  });

  textarea.style.display = "none";

  parentElement.insertBefore(editor.dom, textarea.nextSibling);
  (textarea as any)._codeMirrorInstance = editor;

  editor.dom.style.minHeight = "300px";
  editor.dom.style.width = "100%";

  document
    .querySelectorAll(".editor-buttons button")
    .forEach((button: Element) => {
      button.addEventListener("click", (e: Event) => {
        e.preventDefault();
        const target = e.currentTarget as HTMLButtonElement;
        const param = target.getAttribute("data-insert");
        if (editor && param) {
          const transaction = editor.state.update({
            changes: { from: editor.state.selection.main.head, insert: param },
          });
          editor.dispatch(transaction);
        }
      });
    });

  if (mode === "htmlmixed") {
    editor.dom.addEventListener("keydown", (event: KeyboardEvent) => {
      if (event.ctrlKey && event.key === "i") {
        event.preventDefault();
        formatEditorContent(editor, htmlBeautify);
      }
    });
  }

  if (mode === "css") {
    editor.dom.addEventListener("keydown", (event: KeyboardEvent) => {
      if (event.ctrlKey && event.key === "i") {
        event.preventDefault();
        formatEditorContent(editor, cssBeautify);
      }
    });
  }
}
