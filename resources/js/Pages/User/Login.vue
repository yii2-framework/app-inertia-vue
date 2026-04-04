<script setup>
import { Head, Link, useForm, usePage } from "@inertiajs/vue3";

const page = usePage();

const form = useForm({
    "LoginForm[username]": "",
    "LoginForm[password]": "",
    "LoginForm[rememberMe]": true,
});

const submit = () => {
    form.post("/user/login", {
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
    <Head title="Login to your account" />

    <div class="grow flex items-center justify-center py-8">
        <div
            class="overflow-hidden rounded-2xl shadow-lg dark:shadow-gray-900/50 bg-gray-50 dark:bg-gray-800 w-full max-w-[900px]"
        >
            <div class="flex flex-col md:flex-row">
                <!-- Brand panel -->
                <div
                    class="hidden md:flex md:w-5/12 login-brand-panel text-white"
                >
                    <div
                        class="flex flex-col justify-between p-6 lg:p-8 w-full"
                    >
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
                                Welcome<br />Back
                            </h2>
                            <p class="opacity-75 text-[0.9rem]">
                                Log in to access your Yii2 application and
                                manage your account.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Form panel -->
                <div class="w-full md:w-7/12">
                    <div class="p-6 lg:p-8">
                        <div class="text-center mb-6">
                            <h1
                                class="text-2xl font-bold mb-1 text-gray-900 dark:text-white"
                            >
                                Login to your account
                            </h1>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Enter your credentials to continue
                            </p>
                        </div>

                        <form @submit.prevent="submit">
                            <div class="mb-4">
                                <label
                                    for="login-username"
                                    class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1"
                                    >Your Username</label
                                >
                                <div
                                    :class="[
                                        'flex border rounded-lg overflow-hidden transition-all focus-within:ring-2',
                                        fieldError('username')
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
                                        id="login-username"
                                        v-model="form['LoginForm[username]']"
                                        type="text"
                                        class="w-full py-2.5 pr-3 bg-transparent border-0 outline-none text-gray-900 dark:text-white placeholder-gray-400"
                                        placeholder="admin"
                                        autofocus
                                    />
                                </div>
                                <p
                                    v-if="fieldError('username')"
                                    class="text-red-600 dark:text-red-400 text-sm mt-1"
                                >
                                    {{ fieldError("username") }}
                                </p>
                            </div>

                            <div class="mb-4">
                                <label
                                    for="login-password"
                                    class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1"
                                    >Your Password</label
                                >
                                <div
                                    :class="[
                                        'flex border rounded-lg overflow-hidden transition-all focus-within:ring-2',
                                        fieldError('password')
                                            ? 'border-red-500 focus-within:ring-red-500/25'
                                            : 'border-gray-300 dark:border-gray-600 focus-within:border-primary-500 focus-within:ring-primary-500/25',
                                    ]"
                                >
                                    <span
                                        class="flex items-center justify-center pl-3 pr-2 text-gray-400"
                                        aria-hidden="true"
                                        >&#128274;</span
                                    >
                                    <input
                                        id="login-password"
                                        v-model="form['LoginForm[password]']"
                                        type="password"
                                        class="w-full py-2.5 pr-3 bg-transparent border-0 outline-none text-gray-900 dark:text-white placeholder-gray-400"
                                        placeholder="admin"
                                    />
                                </div>
                                <p
                                    v-if="fieldError('password')"
                                    class="text-red-600 dark:text-red-400 text-sm mt-1"
                                >
                                    {{ fieldError("password") }}
                                </p>
                            </div>

                            <div class="mb-5">
                                <label
                                    class="flex items-center gap-2 cursor-pointer"
                                >
                                    <input
                                        v-model="form['LoginForm[rememberMe]']"
                                        type="checkbox"
                                        class="w-4 h-4 text-primary-600 bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded focus:ring-primary-500 cursor-pointer"
                                    />
                                    <span
                                        class="text-sm text-gray-700 dark:text-gray-300"
                                        >Remember Me</span
                                    >
                                </label>
                            </div>

                            <button
                                type="submit"
                                class="w-full login-btn text-white py-3 rounded-lg text-lg font-semibold cursor-pointer"
                                :disabled="form.processing"
                            >
                                Login
                            </button>
                        </form>

                        <div class="mt-6">
                            <div
                                class="login-footer-divider text-gray-500 dark:text-gray-400"
                            >
                                <span>or</span>
                            </div>
                            <div class="text-center mt-4">
                                <p
                                    class="mb-2 text-sm text-gray-600 dark:text-gray-400"
                                >
                                    Don't have an account?
                                    <Link
                                        href="/user/signup"
                                        class="font-semibold text-primary-600 dark:text-primary-400 hover:underline"
                                    >
                                        Sign up
                                    </Link>
                                </p>
                                <div class="flex justify-center gap-3 text-sm">
                                    <Link
                                        href="/user/request-password-reset"
                                        class="text-primary-600 dark:text-primary-400 hover:underline"
                                    >
                                        Forgot password
                                    </Link>
                                    <span
                                        class="text-gray-300 dark:text-gray-600"
                                        >|</span
                                    >
                                    <Link
                                        href="/user/resend-verification-email"
                                        class="text-primary-600 dark:text-primary-400 hover:underline"
                                    >
                                        Resend verification
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
