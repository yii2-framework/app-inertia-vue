<script setup>
import { Head } from "@inertiajs/vue3";

const extensions = [
  {
    icon: "\u{1F50E}",
    name: "yii2-debug",
    description:
      "Debug toolbar and debugger for Yii2. Inspect logs, database queries, request data, and application performance in real time.",
    url: "https://www.yiiframework.com/extension/yiisoft/yii2-debug",
  },
  {
    icon: "\u2699",
    name: "yii2-gii",
    description:
      "Automatic code generator for models, controllers, CRUD, forms, and modules. Boost your productivity with scaffolding.",
    url: "https://www.yiiframework.com/extension/yiisoft/yii2-gii",
  },
  {
    icon: "\u{1F4CB}",
    name: "yii2-queue",
    description:
      "Asynchronous job queue with support for DB, Redis, AMQP, Beanstalk, and SQS drivers. Run background tasks with ease.",
    url: "https://www.yiiframework.com/extension/yiisoft/yii2-queue",
  },
  {
    icon: "\u26A1",
    name: "yii2-redis",
    description:
      "Redis integration providing cache, session, and ActiveRecord support. Leverage in-memory storage for blazing-fast data access.",
    url: "https://www.yiiframework.com/extension/yiisoft/yii2-redis",
  },
  {
    icon: "\u{1F50D}",
    name: "yii2-elasticsearch",
    description:
      "Elasticsearch integration with ActiveRecord and query builder. Add powerful full-text search capabilities to your application.",
    url: "https://www.yiiframework.com/extension/yiisoft/yii2-elasticsearch",
  },
  {
    icon: "\u2709",
    name: "yii2-symfonymailer",
    description:
      "Email sending integration powered by Symfony Mailer. Compose and deliver rich HTML emails with attachments and templates.",
    url: "https://github.com/yiisoft/yii2-symfonymailer",
  },
];
</script>

<template>
  <Head title="My Yii Application" />

  <div class="grow flex flex-col justify-center">
    <!-- Hero banner -->
    <div
      class="hero-banner text-white rounded-2xl p-8 sm:p-10 mb-6 relative overflow-hidden"
    >
      <img
        src="/images/yii3_full_white_for_dark.svg"
        alt=""
        class="hidden lg:block absolute right-10 top-1/2 -translate-y-1/6 h-[120px] opacity-40"
      />
      <div class="relative">
        <h1
          class="font-display text-4xl sm:text-5xl font-bold mb-4 tracking-tight"
        >
          Build with Yii Framework
        </h1>
        <p class="text-lg opacity-80 mb-6 max-w-[520px] leading-relaxed">
          A high-performance PHP framework best for developing web applications.
          Fast, secure, and professional.
        </p>
        <div class="flex gap-3 flex-wrap">
          <a
            href="https://www.yiiframework.com/doc/guide/2.0/en/start-installation"
            class="bg-white text-gray-900 font-semibold px-6 py-3 rounded-lg hover:bg-gray-100 transition-colors"
            rel="noopener noreferrer"
            target="_blank"
            >Get Started</a
          >
          <a
            href="https://www.yiiframework.com/doc/api/2.0"
            class="border border-white/60 text-white px-6 py-3 rounded-lg hover:bg-white/10 transition-colors"
            rel="noopener noreferrer"
            target="_blank"
            >API Reference</a
          >
        </div>
      </div>
    </div>

    <!-- Extensions grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <div v-for="ext in extensions" :key="ext.name">
        <div
          class="extension-card flex flex-col h-full bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700"
        >
          <div class="p-5 grow">
            <div class="flex items-center mb-3">
              <span class="text-2xl leading-none" aria-hidden="true">{{
                ext.icon
              }}</span>
              <h3
                class="font-display text-sm font-bold ml-2 text-gray-900 dark:text-white"
              >
                {{ ext.name }}
              </h3>
            </div>
            <p class="text-gray-500 dark:text-gray-400 text-sm leading-relaxed">
              {{ ext.description }}
            </p>
          </div>
          <div class="px-5 pb-5">
            <a
              :href="ext.url"
              :aria-label="`Learn more about ${ext.name}`"
              class="text-sm font-medium text-primary-600 dark:text-primary-400 hover:underline"
              rel="noopener noreferrer"
              target="_blank"
              >Learn more &rarr;</a
            >
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
