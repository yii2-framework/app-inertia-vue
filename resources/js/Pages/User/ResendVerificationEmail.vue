<script setup>
import { Head, Link, useForm, usePage } from "@inertiajs/vue3";

const page = usePage();

const form = useForm({
  "ResendVerificationEmailForm[email]": "",
});

const submit = () => {
  form.post("/user/resend-verification-email", {
    preserveScroll: true,
  });
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
  <Head title="Resend verification email" />

  <div class="grow flex items-center justify-center py-8">
    <div
      class="overflow-hidden rounded-2xl shadow-lg dark:shadow-gray-900/50 bg-gray-50 dark:bg-gray-800 w-full max-w-[900px]"
    >
      <div class="flex flex-col md:flex-row">
        <!-- Brand panel -->
        <div class="hidden md:flex md:w-5/12 login-brand-panel text-white">
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
                Verify Your<br />Email
              </h2>
              <p class="opacity-75 text-[0.9rem]">
                We will send a new verification email to confirm your account.
              </p>
            </div>
          </div>
        </div>

        <!-- Form panel -->
        <div class="w-full md:w-7/12">
          <div class="p-6 lg:p-8">
            <div class="text-center mb-6">
              <div class="md:hidden mb-4">
                <img
                  src="/images/yii3_full_black_for_light.svg"
                  alt="Yii Framework"
                  class="dark:invert mx-auto"
                  height="36"
                />
              </div>
              <h1 class="text-2xl font-bold mb-1 text-gray-900 dark:text-white">
                Resend verification email
              </h1>
              <p class="text-sm text-gray-500 dark:text-gray-400">
                Enter your email to receive a new verification link
              </p>
            </div>

            <form @submit.prevent="submit">
              <div class="mb-5">
                <label
                  for="resend-email"
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
                    id="resend-email"
                    v-model="form['ResendVerificationEmailForm[email]']"
                    type="email"
                    class="w-full py-2.5 pr-3 bg-transparent border-0 outline-none text-gray-900 dark:text-white placeholder-gray-400"
                    placeholder="email@example.com"
                    autofocus
                  />
                </div>
                <p
                  v-if="fieldError('email')"
                  class="text-red-600 dark:text-red-400 text-sm mt-1"
                >
                  {{ fieldError("email") }}
                </p>
              </div>

              <button
                type="submit"
                class="w-full login-btn text-white py-3 rounded-lg text-lg font-semibold cursor-pointer"
                :disabled="form.processing"
              >
                Send
              </button>
            </form>

            <p
              class="text-center mt-4 text-sm text-gray-500 dark:text-gray-400"
            >
              Already verified?
              <Link
                href="/user/login"
                class="text-primary-600 dark:text-primary-400 hover:underline"
              >
                Login
              </Link>
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
