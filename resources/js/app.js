import { createApp, h } from "vue";
import { createInertiaApp, router } from "@inertiajs/vue3";
import Layout from "./Components/Layout.vue";
import "../css/app.css";

const appEl = document.getElementById("app");
const payloadEl = appEl?.querySelector('script[type="application/json"]');

if (!appEl || !payloadEl?.textContent) {
  throw new Error("Inertia bootstrap payload not found in `#app`.");
}

const pageData = JSON.parse(payloadEl.textContent);

// CSRF: the server uses validateCsrfHeaderOnly=true — it only checks that the
// header is present, not its value.  CORS prevents cross-origin JS from setting
// custom headers, so presence alone proves same-origin.
router.on("before", (event) => {
  event.detail.visit.headers["X-CSRF-Token"] = "same-origin";
});

createInertiaApp({
  id: "app",
  page: pageData,
  resolve: (name) => {
    const pages = import.meta.glob("./Pages/**/*.vue", { eager: true });
    const page = pages[`./Pages/${name}.vue`];

    if (!page) {
      throw new Error(`Page component "${name}" not found.`);
    }

    page.default.layout = page.default.layout ?? Layout;

    return page;
  },
  setup({ el, App, props, plugin }) {
    createApp({ render: () => h(App, props) })
      .use(plugin)
      .mount(el);
  },
});
