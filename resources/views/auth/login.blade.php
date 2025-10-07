@extends('layouts.app')
@section('title', 'Login Home Page')
@section('content')
    <x-nav-tedja />
    <main class="flex min-h-screen">
        <form action="{{ route('login') }}" method="POST"
            class="flex items-center flex-1 pl-[calc(((100%-1280px)/2)+75px)] pt-[114px]">
            @csrf
            <div
                class="flex flex-col h-fit w-[500px] shrink-0 rounded-[20px] border border-tedja-border p-[30px] gap-5 bg-white">
                <h1 class="font-bold text-[28px] leading-[42px]">Sign In to My Account</h1>
                <div class="flex flex-col gap-2">
                    <p class="font-semibold">Email Address</p>
                    <label class="relative">
                        <img src="assets/images/icons/sms.svg"
                            class="absolute size-6 transform -translate-y-1/2 top-1/2 left-5" alt="icon">
                        <input type="email" name="email"
                            class="appearance-none outline-none w-full rounded-full ring-1 ring-tedja-border py-[14px] pl-[54px] px-5 font-semibold placeholder:font-normal focus:ring-1 focus:ring-tedja-blue transition-all duration-300"
                            placeholder="Type your email address">
                    </label>
                    <x-input-error :messages="$errors->get('email')" class="text-sm text-tedja-red" />
                </div>
                <div class="flex flex-col gap-2">
                    <p class="font-semibold">Password</p>
                    <label class="relative">
                        <img src="assets/images/icons/lock.svg"
                            class="absolute size-6 transform -translate-y-1/2 top-1/2 left-5" alt="icon">
                        <input type="password" name="password"
                            class="appearance-none outline-none w-full rounded-full ring-1 ring-tedja-border py-[14px] pl-[54px] px-5 font-semibold placeholder:font-normal focus:ring-1 focus:ring-tedja-blue transition-all duration-300"
                            placeholder="Type your password">
                    </label>
                    <x-input-error :messages="$errors->get('password')" class="text-sm text-tedja-red" />
                    <a href="#" class="hover:underline">Forgot my password</a>
                </div>
                <button type="submit"
                    class="rounded-full py-[14px] px-5 bg-tedja-green w-full text-center font-semibold">Sign In to Manage
                    Mortgages</button>
            </div>
        </form>
        <div class="relative flex w-full max-w-[640px]">
            <div class="fixed top-0 h-screen w-full max-w-[640px] overflow-hidden">
                <img src="assets/images/backgrounds/login-banner.png" class="w-full h-full object-cover" alt="banner">
                <div class="absolute bottom-0 w-full px-[30px] pb-[30px]">
                    <div class="flex flex-col rounded-[30px] border border-tedja-border p-4 gap-[14px] bg-white">
                        <div class="flex">
                            <img src="assets/images/icons/Star 1.svg" class="flex shrink-0" alt="star">
                            <img src="assets/images/icons/Star 1.svg" class="flex shrink-0" alt="star">
                            <img src="assets/images/icons/Star 1.svg" class="flex shrink-0" alt="star">
                            <img src="assets/images/icons/Star 1.svg" class="flex shrink-0" alt="star">
                            <img src="assets/images/icons/Star 1.svg" class="flex shrink-0" alt="star">
                        </div>
                        <p class="font-semibold leading-[28px]">Tedja by Angga membantu kami mendapatkan rumah idaman dengan
                            interest yang rendah, gaji UMR juga bisa hidup bahagia!</p>
                        <div class="flex items-center gap-[14px]">
                            <div class="flex size-[60px] rounded-full overflow-hidden">
                                <img src="assets/images/photos/profile.png" class="w-full h-full object-cover"
                                    alt="photo profile">
                            </div>
                            <div>
                                <p class="font-semibold">Sarina Dwi</p>
                                <p class="text-sm text-tedja-secondary">House Designer</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
