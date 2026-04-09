<script setup>
import { usePage } from "@inertiajs/vue3";

const page = usePage();
const year = new Date().getFullYear();
</script>

<template>
  <footer
    class="mt-auto py-4 bg-gray-50 dark:bg-gray-950 border-t border-gray-200 dark:border-gray-800"
  >
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div
        class="flex flex-col md:flex-row justify-between items-center gap-2 text-sm text-gray-500 dark:text-gray-400"
      >
        <span>&copy; {{ page.props.appName }} {{ year }}</span>
        <a
          href="https://www.yiiframework.com/"
          rel="external"
          aria-label="Powered by Yii Framework"
          class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 no-underline transition-colors"
        >
          Powered by
          <img
            src="/images/yii3_full_for_light.svg"
            alt=""
            aria-hidden="true"
            class="inline-block align-text-bottom h-7 dark:hidden"
          />
          <img
            src="/images/yii3_full_for_dark.svg"
            alt=""
            aria-hidden="true"
            class="hidden dark:inline-block align-text-bottom h-7"
          />
        </a>
      </div>
    </div>
  </footer>
</template>
