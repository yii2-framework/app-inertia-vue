<script setup>
import { Head, useForm, usePage, Link } from "@inertiajs/vue3";
import VueTurnstile from "vue-turnstile";

const page = usePage();

const form = useForm({
  "ContactForm[name]": "",
  "ContactForm[email]": "",
  "ContactForm[phone]": "",
  "ContactForm[subject]": "",
  "ContactForm[body]": "",
  "ContactForm[turnstileToken]": "",
});

const submit = () => {
  form.post("/site/contact", {
    preserveScroll: true,
  });
};

const maskPhone = (event) => {
  let value = event.target.value.replace(/\D/g, "");

  if (value.length > 10) {
    value = value.slice(0, 10);
  }

  if (value.length >= 7) {
    value = `(${value.slice(0, 3)}) ${value.slice(3, 6)}-${value.slice(6)}`;
  } else if (value.length >= 4) {
    value = `(${value.slice(0, 3)}) ${value.slice(3)}`;
  } else if (value.length >= 1) {
    value = `(${value}`;
  }

  form["ContactForm[phone]"] = value;
};

const fieldError = (field) => {
  const error = page.props.errors?.[field];
  if (Array.isArray(error)) {
    return error[0] ?? null;
  }
  return typeof error === "string" ? error : null;
};
</script>

<template>
  <Head title="Contact us" />

  <!-- Success state -->
  <div
    v-if="page.props.flash?.success"
    class="grow flex items-center justify-center py-4"
    role="status"
    aria-live="polite"
  >
    <div
      class="overflow-hidden rounded-2xl shadow-lg dark:shadow-gray-900/50 bg-gray-50 dark:bg-gray-800 w-full max-w-[960px]"
    >
      <div class="flex flex-col md:flex-row">
        <!-- Brand panel -->
        <div class="hidden md:flex md:w-1/3 login-brand-panel text-white">
          <div class="flex flex-col justify-between p-6 lg:p-8 w-full">
            <div>
              <img
                src="/images/yii3_full_white_for_dark.svg"
                alt="Yii Framework"
                class="mb-6"
                height="40"
              />
            </div>
            <div>
              <h2
                class="font-display font-bold mb-3 text-[1.75rem] leading-tight"
              >
                Message<br />Received.
              </h2>
              <p class="opacity-75 text-[0.9rem]">
                Thanks for reaching out. We will reply as soon as we can.
              </p>
            </div>
          </div>
        </div>

        <!-- Content panel -->
        <div class="w-full md:w-2/3 flex items-center justify-center">
          <div class="p-6 lg:p-8 text-center w-full max-w-md">
            <h1 class="text-2xl font-bold mb-2 text-gray-900 dark:text-white">
              Thanks, we got it.
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
              Your message has been delivered to the team. Want to send another
              note?
            </p>
            <Link
              href="/site/contact"
              class="inline-block w-full login-btn text-white font-medium px-6 py-3 rounded-lg transition-colors"
            >
              Send another message
            </Link>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Contact form -->
  <div v-else class="grow flex items-center justify-center py-4">
    <div
      class="overflow-hidden rounded-2xl shadow-lg dark:shadow-gray-900/50 bg-gray-50 dark:bg-gray-800 w-full max-w-[960px]"
    >
      <div class="flex flex-col md:flex-row">
        <!-- Brand panel -->
        <div class="hidden md:flex md:w-1/3 login-brand-panel text-white">
          <div class="flex flex-col justify-between p-6 lg:p-8 w-full">
            <div>
              <img
                src="/images/yii3_full_white_for_dark.svg"
                alt="Yii Framework"
                class="mb-6"
                height="40"
              />
            </div>
            <div>
              <h2
                class="font-display font-bold mb-3 text-[1.75rem] leading-tight"
              >
                Get In<br />Touch
              </h2>
              <p class="opacity-75 text-[0.9rem]">
                Have a question or business inquiry? We would love to hear from
                you.
              </p>
            </div>
          </div>
        </div>

        <!-- Form panel -->
        <div class="w-full md:w-2/3">
          <div class="p-6 lg:p-8">
            <div class="text-center mb-6">
              <h1 class="text-2xl font-bold mb-1 text-gray-900 dark:text-white">
                Contact us
              </h1>
              <p class="text-sm text-gray-500 dark:text-gray-400">
                Fill out the form below and we will get back to you
              </p>
            </div>

            <form @submit.prevent="submit">
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                  <label
                    for="contact-name"
                    class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1"
                    >Your Name</label
                  >
                  <div
                    :class="[
                      'flex border rounded-lg overflow-hidden transition-all focus-within:ring-2',
                      fieldError('name')
                        ? 'border-red-500 focus-within:ring-red-500/25'
                        : 'border-gray-300 dark:border-gray-600 focus-within:border-primary-500 focus-within:ring-primary-500/25',
                    ]"
                  >
                    <span
                      class="flex items-center justify-center pl-3 pr-2 text-gray-400"
                      aria-hidden="true"
                      >&#128100;</span
                    >
                    <input
                      id="contact-name"
                      v-model="form['ContactForm[name]']"
                      type="text"
                      :aria-invalid="Boolean(fieldError('name'))"
                      :aria-describedby="
                        fieldError('name') ? 'contact-name-error' : undefined
                      "
                      class="w-full py-2.5 pr-3 bg-transparent border-0 outline-none text-gray-900 dark:text-white placeholder-gray-400"
                      placeholder="Name"
                      autofocus
                    />
                  </div>
                  <p
                    v-if="fieldError('name')"
                    id="contact-name-error"
                    class="text-red-600 dark:text-red-400 text-sm mt-1"
                  >
                    {{ fieldError("name") }}
                  </p>
                </div>

                <div>
                  <label
                    for="contact-email"
                    class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1"
                    >Your Email</label
                  >
                  <div
                    :class="[
                      'flex border rounded-lg overflow-hidden transition-all focus-within:ring-2',
                      fieldError('email')
                        ? 'border-red-500 focus-within:ring-red-500/25'
                        : 'border-gray-300 dark:border-gray-600 focus-within:border-primary-500 focus-within:ring-primary-500/25',
                    ]"
                  >
                    <span
                      class="flex items-center justify-center pl-3 pr-2 text-gray-400"
                      aria-hidden="true"
                      >&#9993;</span
                    >
                    <input
                      id="contact-email"
                      v-model="form['ContactForm[email]']"
                      type="email"
                      :aria-invalid="Boolean(fieldError('email'))"
                      :aria-describedby="
                        fieldError('email') ? 'contact-email-error' : undefined
                      "
                      class="w-full py-2.5 pr-3 bg-transparent border-0 outline-none text-gray-900 dark:text-white placeholder-gray-400"
                      placeholder="email@example.com"
                    />
                  </div>
                  <p
                    v-if="fieldError('email')"
                    id="contact-email-error"
                    class="text-red-600 dark:text-red-400 text-sm mt-1"
                  >
                    {{ fieldError("email") }}
                  </p>
                </div>
              </div>

              <div class="mb-4">
                <label
                  for="contact-phone"
                  class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1"
                  >Phone</label
                >
                <div
                  :class="[
                    'flex border rounded-lg overflow-hidden transition-all focus-within:ring-2',
                    fieldError('phone')
                      ? 'border-red-500 focus-within:ring-red-500/25'
                      : 'border-gray-300 dark:border-gray-600 focus-within:border-primary-500 focus-within:ring-primary-500/25',
                  ]"
                >
                  <span
                    class="flex items-center justify-center pl-3 pr-2 text-gray-400"
                    aria-hidden="true"
                    >&#128222;</span
                  >
                  <input
                    id="contact-phone"
                    :value="form['ContactForm[phone]']"
                    type="tel"
                    :aria-invalid="Boolean(fieldError('phone'))"
                    :aria-describedby="
                      fieldError('phone') ? 'contact-phone-error' : undefined
                    "
                    class="w-full py-2.5 pr-3 bg-transparent border-0 outline-none text-gray-900 dark:text-white placeholder-gray-400"
                    placeholder="(999) 999-9999"
                    @input="maskPhone"
                  />
                </div>
                <p
                  v-if="fieldError('phone')"
                  id="contact-phone-error"
                  class="text-red-600 dark:text-red-400 text-sm mt-1"
                >
                  {{ fieldError("phone") }}
                </p>
              </div>

              <div class="mb-4">
                <label
                  for="contact-subject"
                  class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1"
                  >Subject</label
                >
                <div
                  :class="[
                    'flex border rounded-lg overflow-hidden transition-all focus-within:ring-2',
                    fieldError('subject')
                      ? 'border-red-500 focus-within:ring-red-500/25'
                      : 'border-gray-300 dark:border-gray-600 focus-within:border-primary-500 focus-within:ring-primary-500/25',
                  ]"
                >
                  <span
                    class="flex items-center justify-center pl-3 pr-2 text-gray-400"
                    aria-hidden="true"
                    >&#128172;</span
                  >
                  <input
                    id="contact-subject"
                    v-model="form['ContactForm[subject]']"
                    type="text"
                    :aria-invalid="Boolean(fieldError('subject'))"
                    :aria-describedby="
                      fieldError('subject')
                        ? 'contact-subject-error'
                        : undefined
                    "
                    class="w-full py-2.5 pr-3 bg-transparent border-0 outline-none text-gray-900 dark:text-white placeholder-gray-400"
                    placeholder="Subject"
                  />
                </div>
                <p
                  v-if="fieldError('subject')"
                  id="contact-subject-error"
                  class="text-red-600 dark:text-red-400 text-sm mt-1"
                >
                  {{ fieldError("subject") }}
                </p>
              </div>

              <div class="mb-4">
                <label
                  for="contact-body"
                  class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1"
                  >Message</label
                >
                <textarea
                  id="contact-body"
                  v-model="form['ContactForm[body]']"
                  :aria-invalid="Boolean(fieldError('body'))"
                  :aria-describedby="
                    fieldError('body') ? 'contact-body-error' : undefined
                  "
                  :class="[
                    'w-full py-2.5 px-3 bg-transparent border rounded-lg outline-none text-gray-900 dark:text-white placeholder-gray-400 h-[120px] transition-all focus:ring-2',
                    fieldError('body')
                      ? 'border-red-500 focus:ring-red-500/25'
                      : 'border-gray-300 dark:border-gray-600 focus:border-primary-500 focus:ring-primary-500/25',
                  ]"
                  placeholder="Your message..."
                />
                <p
                  v-if="fieldError('body')"
                  id="contact-body-error"
                  class="text-red-600 dark:text-red-400 text-sm mt-1"
                >
                  {{ fieldError("body") }}
                </p>
              </div>

              <div class="flex items-center justify-between gap-4 flex-wrap">
                <VueTurnstile
                  v-model="form['ContactForm[turnstileToken]']"
                  :site-key="page.props.turnstileSiteKey"
                  theme="auto"
                />
                <button
                  type="submit"
                  class="login-btn text-white px-6 py-2.5 rounded-lg cursor-pointer"
                  :disabled="
                    form.processing || !form['ContactForm[turnstileToken]']
                  "
                >
                  Submit
                </button>
              </div>
              <p
                v-if="fieldError('turnstileToken')"
                id="contact-turnstile-error"
                role="alert"
                aria-live="polite"
                class="text-red-600 dark:text-red-400 text-sm mt-1"
              >
                {{ fieldError("turnstileToken") }}
              </p>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
