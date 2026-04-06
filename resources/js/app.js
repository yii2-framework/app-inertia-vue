import { createApp, h } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";
import Layout from "./Components/Layout.vue";
import "../css/app.css";

const appEl = document.getElementById("app");
const payloadEl = appEl?.querySelector('script[type="application/json"]');

if (!appEl || !payloadEl?.textContent) {
  throw new Error("Inertia bootstrap payload not found in `#app`.");
}

const pageData = JSON.parse(payloadEl.textContent);

createInertiaApp({
  id: "app",
  page: pageData,
  progress: {
    delay: 250,
    color: "#1a56db",
    includeCSS: true,
    showSpinner: true,
  },
  defaults: {
    viewTransition: true,
  },
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
