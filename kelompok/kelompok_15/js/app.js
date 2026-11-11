import { render } from "./templates.js";
import { bindControls } from "./controls.js";

const { layout, api } = document.body.dataset;
const endpoint = new URL(api, location.href);
endpoint.search = location.search;
const root = new URL("../", import.meta.url);
const pageTemplate = document.getElementById("page-template").textContent;
const templates = new Map();

// LOGIKA HALAMAN & LAYOUT UMUM - ORANG 1
function loadTemplate(name) {
  if (!templates.has(name)) {
    templates.set(
      name,
      fetch(new URL("templates/" + name + ".html", root)).then((response) => {
        if (!response.ok) throw new Error("Template halaman gagal dimuat.");
        return response.text();
      }),
    );
  }
  return templates.get(name);
}

async function showPage(url, options) {
  const response = await fetch(url, options);
  if (!response.headers.get("Content-Type")?.includes("application/json")) {
    const message = await response.text();
    throw new Error(
      response.status >= 500
        ? "Server gagal memuat data. Periksa koneksi database."
        : message,
    );
  }
  const data = await response.json();
  if (data.redirect) {
    const next = new URL(data.redirect, location.href);
    if (
      next.pathname === location.pathname &&
      next.search === location.search
    ) {
      history.replaceState(null, "", next);
      location.reload();
    } else {
      location.assign(next);
    }
    return;
  }
  const page = data.missing
    ? await loadTemplate(
        endpoint.pathname.split("/").pop().replace(".php", "-404"),
      )
    : pageTemplate;
  const partials = {};
  for (const name of ["card", "compact"]) {
    if (!page.includes("{{>" + name + "}}")) continue;
    const template = await loadTemplate(name);
    partials[name] = (item) => render(template, item.card, root.pathname);
  }
  const content = render(page, data.content ?? {}, root.pathname, partials);
  document.body.innerHTML =
    layout === "login"
      ? content
      : render(await loadTemplate(layout), data.layout, root.pathname, {
          content: () => content,
        });
  document.title = data.title;
  bindControls();
  if (location.hash)
    document.getElementById(location.hash.slice(1))?.scrollIntoView();
}

function showError(error) {
  document.body.textContent = error.message;
}

document.addEventListener("submit", async (event) => {
  const form = event.target;
  if (event.defaultPrevented || form.method.toLowerCase() !== "post") return;
  event.preventDefault();
  if (form.dataset.pending) return;
  form.dataset.pending = "true";
  try {
    const body = event.submitter
      ? new FormData(form, event.submitter)
      : new FormData(form);
    await showPage(form.hasAttribute("action") ? form.action : endpoint, {
      method: "POST",
      body,
    });
  } catch (error) {
    showError(error);
  } finally {
    delete form.dataset.pending;
  }
});

showPage(endpoint).catch(showError);
