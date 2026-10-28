<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Sebagai Owner Page - Kopi Tiga Titik</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=AR+One+Sans:wght@400;500;600;700&family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

    <!-- Tailwind CSS via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 min-h-screen flex justify-center items-start py-6 antialiased">
<div class="w-96 h-[780px] pb-8 bg-purple-50 shadow-2xl inline-flex flex-col justify-start items-start overflow-hidden">
    <div
        class="self-stretch h-48 relative bg-blue-800 rounded-bl-3xl rounded-br-3xl shadow-[0px_2px_4px_-2px_rgba(0,0,0,0.10)] shadow-md overflow-hidden">
        <div class="w-96 h-14 left-0 top-0 absolute bg-white inline-flex justify-between items-center">
            <div class="size- flex justify-start items-center gap-2">
                <div
                    class="size-9 bg-white/10 rounded-xl outline outline-1 outline-offset-[-1px] outline-white/20 backdrop-blur-xs flex justify-center items-center">
                    <img class="size-8 rounded-lg" src="{{ asset('images/logo.png') }}" />
                </div>
                <div class="size- inline-flex flex-col justify-start items-start">
                    <div class="justify-center text-sky-900 text-xl font-bold font-['Plus_Jakarta_Sans'] leading-6">Kopi
                        Tiga Titik</div>
                </div>
            </div>
        </div>
        <div
            class="w-48 h-20 pl-0.5 pt-[4.79px] pb-[3.21px] left-[295.80px] top-[43px] absolute origin-top-left rotate-12 opacity-10 inline-flex flex-col justify-start items-start">
            <div
                class="justify-center text-white text-8xl font-normal font-['Material_Symbols_Outlined'] leading-[96px]">
                coffee</div>
        </div>
        <div class="size-96 pt-px left-0 top-[68px] absolute inline-flex flex-col justify-start items-start gap-1">
            <div
                class="size- px-2.5 py-1 bg-white/20 rounded-full outline outline-1 outline-offset-[-1px] outline-white/20 backdrop-blur-md inline-flex justify-start items-center gap-1.5">
                <div class="size-1.5 bg-blue-400 rounded-full"></div>
                <div class="size- inline-flex flex-col justify-start items-start">
                    <div
                        class="justify-center text-white text-[10px] font-bold font-['Inter'] uppercase leading-4 tracking-wide">
                        Street coffe mojokerto</div>
                </div>
            </div>
            <div class="self-stretch pt-2 flex flex-col justify-start items-start">
                <div
                    class="self-stretch justify-center text-white text-2xl font-bold font-['Plus_Jakarta_Sans'] leading-8">
                    Selamat Datang </div>
            </div>
            <div class="w-72 max-w-72 flex flex-col justify-start items-start">
                <div class="justify-center text-indigo-300 text-xs font-normal font-['Inter'] leading-4">Silakan masuk
                    untuk mulai memesan <br />kopi botol favoritmu</div>
            </div>
        </div>
    </div>
    <div class="self-stretch h-[561px] relative">
        <div class="w-96 h-[573px] left-0 top-[-12px] absolute">
            <div class="w-96 pb-4 left-[16px] top-0 absolute inline-flex flex-col justify-start items-start">
                <div
                    class="self-stretch p-1 bg-indigo-100 rounded-xl shadow-[0px_1px_2px_0px_rgba(0,0,0,0.05)] outline outline-1 outline-offset-[-1px] outline-slate-300/30 inline-flex justify-start items-center">
                    <div
                        class="flex-1 py-2 bg-blue-800 rounded-lg shadow-[0px_1px_2px_0px_rgba(0,0,0,0.05)] flex justify-center items-center gap-1.5">
                        <div class="size- inline-flex flex-col justify-start items-center">
                            <span class="material-symbols-outlined text-white text-base select-none">login</span>
                        </div>
                        <div class="size- inline-flex flex-col justify-start items-center">
                            <div
                                class="text-center justify-center text-white text-sm font-bold font-['Inter'] leading-5 tracking-tight">
                                Masuk</div>
                        </div>
                    </div>
                    <div class="flex-1 py-2 rounded-lg flex justify-center items-center gap-1.5">
                        <div class="size- inline-flex flex-col justify-start items-center">
                            <span class="material-symbols-outlined text-gray-700 text-base select-none">person_add</span>
                        </div>
                        <div class="size- inline-flex flex-col justify-start items-center">
                            <div
                                class="text-center justify-center text-gray-700 text-sm font-medium font-['Inter'] leading-5 tracking-tight">
                                Daftar Akun Baru</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="w-96 pb-4 left-[16px] top-[62px] absolute inline-flex flex-col justify-start items-start">
                <div
                    class="self-stretch p-4 bg-white rounded-xl shadow-[0px_1px_2px_0px_rgba(0,0,0,0.05)] outline outline-1 outline-offset-[-1px] outline-slate-300/40 flex flex-col justify-start items-start gap-3">
                    <div class="self-stretch flex flex-col justify-start items-start gap-1.5">
                        <div class="self-stretch flex flex-col justify-start items-start">
                            <div
                                class="self-stretch justify-center text-slate-900 text-xs font-semibold font-['Inter'] leading-4 tracking-tight">
                                Nomor WhatsApp / Email</div>
                        </div>
                        <div class="self-stretch relative inline-flex justify-start items-center">
                            <div
                                class="flex-1 h-11 pl-10 pr-3 py-3 bg-white rounded-xl outline outline-1 outline-offset-[-1px] outline-slate-300/60 inline-flex flex-col justify-start items-start overflow-hidden">
                                <div class="self-stretch flex flex-col justify-start items-start overflow-hidden">
                                    <div
                                        class="self-stretch justify-center text-gray-500/70 text-sm font-normal font-['Inter']">
                                        owner1@gmail.com</div>
                                </div>
                            </div>
                            <span class="material-symbols-outlined text-gray-400 text-lg left-[14px] top-[13px] absolute select-none">smartphone</span>
                        </div>
                    </div>
                    <div class="self-stretch flex flex-col justify-start items-start gap-1.5">
                        <div class="self-stretch flex flex-col justify-start items-start">
                            <div
                                class="self-stretch justify-center text-slate-900 text-xs font-semibold font-['Inter'] leading-4 tracking-tight">
                                Kata Sandi</div>
                        </div>
                        <div class="self-stretch relative inline-flex justify-start items-center">
                            <div
                                class="flex-1 h-11 pl-10 pr-11 py-3 bg-white rounded-xl outline outline-1 outline-offset-[-1px] outline-slate-300/60 inline-flex flex-col justify-start items-start overflow-hidden">
                                <div class="self-stretch flex flex-col justify-start items-start overflow-hidden">
                                    <div
                                        class="self-stretch justify-center text-gray-500/70 text-sm font-normal font-['Inter']">
                                        owner1</div>
                                </div>
                            </div>
                            <span class="material-symbols-outlined text-gray-400 text-lg left-[14px] top-[13px] absolute select-none">lock</span>
                            <div
                                class="size- px-1 pt-1 pb-3 left-[287.67px] top-[4px] absolute rounded-lg inline-flex flex-col justify-center items-center">
                                <div class="size- inline-flex justify-center items-start">
                                    <div class="w-5 h-3 bg-gray-500"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="self-stretch h-5 relative">
                        <div
                            class="size- left-[219.78px] top-[4px] absolute inline-flex flex-col justify-start items-start">
                            <div
                                class="justify-center text-blue-800 text-xs font-semibold font-['Inter'] leading-4 tracking-tight">
                                Lupa Kata Sandi?</div>
                        </div>
                    </div>
                    <div
                        class="self-stretch h-14 pt-1 relative bg-blue-800 rounded-xl inline-flex justify-center items-center gap-2">
                        <div
                            class="w-80 h-12 left-0 top-[4px] absolute bg-white/0 rounded-xl shadow-[0px_2px_4px_-2px_rgba(0,0,0,0.10)] shadow-md">
                        </div>
                        <div class="size- inline-flex flex-col justify-start items-center">
                            <div
                                class="text-center justify-center text-white text-sm font-bold font-['Inter'] leading-5 tracking-tight">
                                Masuk ke Akun</div>
                        </div>
                        <div class="size- inline-flex flex-col justify-start items-center">
                            <span class="material-symbols-outlined text-white text-base select-none">arrow_forward</span>
                        </div>
                    </div>
                </div>
            </div>
            <div
                class="w-96 px-2 pb-[3px] left-[11px] top-[429px] absolute inline-flex flex-col justify-start items-center gap-3">
                <div class="self-stretch flex flex-col justify-start items-center">
                    <div class="text-center justify-center text-gray-500 text-xs font-normal font-['Inter'] leading-5">
                        Dengan masuk, Anda menyetujui syarat & ketentuan<br />pemesanan Kopi Tiga Titik Mojokerto.</div>
                </div>
                <div
                    class="size- px-3 py-1 bg-indigo-100/60 rounded-full outline outline-1 outline-offset-[-1px] outline-slate-300/40 inline-flex justify-center items-center gap-1.5">
                    <div class="size- inline-flex flex-col justify-start items-center">
                        <span class="material-symbols-outlined text-blue-800 text-sm select-none">storefront</span>
                    </div>
                    <div class="size- inline-flex flex-col justify-start items-center">
                        <div
                            class="text-center justify-center text-gray-700 text-[10px] font-semibold font-['Inter'] leading-4 tracking-wide">
                            @kopitigatitik_ • Seduhan Asli Mojokerto</div>
                    </div>
                </div>
            </div>
        </div>
        <div
            class="size- px-3 py-1.5 left-[33px] top-[352px] absolute bg-gray-500 rounded-[20px] inline-flex justify-center items-center gap-2.5">
            <div class="justify-start text-white text-xs font-semibold font-['AR_One_Sans']">Pembeli</div>
        </div>
        <div
            class="size- px-3 py-1.5 left-[160px] top-[352px] absolute bg-blue-800 rounded-[20px] inline-flex justify-center items-center gap-2.5">
            <div class="justify-start text-white text-xs font-semibold font-['AR_One_Sans']">Owner</div>
        </div>
        <div
            class="size- px-3 py-1.5 left-[286px] top-[352px] absolute bg-gray-500 rounded-[20px] inline-flex justify-center items-center gap-2.5">
            <div class="justify-start text-white text-xs font-semibold font-['AR_One_Sans']">Kurir</div>
        </div>
    </div>
</div>
</body>
</html>
