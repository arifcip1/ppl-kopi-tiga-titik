<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register Page - Kopi Tiga Titik</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=AR+One+Sans:wght@400;500;600;700&family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

    <!-- Tailwind CSS via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 min-h-screen flex justify-center items-start py-6 antialiased">
<div class="w-96 pb-8 bg-purple-50 shadow-2xl inline-flex flex-col justify-start items-start overflow-hidden">
    <div class="self-stretch h-44 relative bg-blue-800 rounded-bl-3xl rounded-br-3xl">
        <div class="w-96 h-44 left-0 top-0 absolute bg-white/0 shadow-[0px_2px_4px_-2px_rgba(0,0,0,0.10)] shadow-md">
        </div>
        <div class="w-96 h-9 left-0 top-0 absolute bg-white">
            <div
                class="size-9 left-0 top-0 absolute bg-white/10 rounded-full backdrop-blur-xs inline-flex justify-center items-center">
                <div class="size- inline-flex flex-col justify-start items-start">
                    <div class="size-3 bg-sky-900"></div>
                </div>
            </div>
            <div class="size- left-[119px] top-[2px] absolute inline-flex justify-start items-center gap-1.5">
                <img class="size-8 rounded-lg" src="{{ asset('images/logo.png') }}" />
                <div class="size- inline-flex flex-col justify-start items-start">
                    <div class="justify-center text-sky-900 text-base font-bold font-['Plus_Jakarta_Sans'] leading-6">
                        Kopi Tiga Titik</div>
                </div>
            </div>
        </div>
        <div class="w-96 pt-px left-[16px] top-[80px] absolute inline-flex flex-col justify-start items-start gap-2">
            <div
                class="size- px-2.5 py-1 bg-blue-900/40 rounded-full outline outline-1 outline-offset-[-1px] outline-blue-400/20 inline-flex justify-start items-center gap-1.5">
                <div class="size-1.5 bg-blue-400 rounded-full"></div>
                <div class="size- inline-flex flex-col justify-start items-start">
                    <div
                        class="justify-center text-white text-[10px] font-bold font-['Inter'] uppercase leading-4 tracking-wide">
                        MOJOKERTO STREET COFFEE</div>
                </div>
            </div>
            <div class="self-stretch flex flex-col justify-start items-start">
                <div
                    class="self-stretch justify-center text-white text-2xl font-bold font-['Plus_Jakarta_Sans'] leading-8">
                    Buat Akun Pembeli Baru</div>
            </div>
        </div>
    </div>
    <div class="self-stretch h-[607px] relative">
        <div class="w-96 px-4 pb-[1.50px] left-0 top-[-12px] absolute inline-flex flex-col justify-start items-start">
            <div
                class="self-stretch p-4 relative bg-white rounded-2xl outline outline-1 outline-offset-[-1px] outline-slate-300/40 flex flex-col justify-start items-start gap-4">
                <div
                    class="w-96 h-[501px] left-0 top-0 absolute bg-white/0 rounded-2xl shadow-[0px_4px_16px_-4px_rgba(15,23,42,0.06)]">
                </div>
                <div class="self-stretch flex flex-col justify-start items-start gap-1">
                    <div class="self-stretch flex flex-col justify-start items-start">
                        <div class="self-stretch justify-center"><span
                                class="text-gray-700 text-xs font-semibold font-['Inter'] leading-4 tracking-tight">Nama
                                Lengkap </span><span
                                class="text-red-700 text-xs font-semibold font-['Inter'] leading-4 tracking-tight">*</span>
                        </div>
                    </div>
                    <div
                        class="self-stretch h-11 px-3 bg-purple-50 rounded-xl outline outline-1 outline-offset-[-1px] outline-slate-300/60 inline-flex justify-start items-center">
                        <div class="size- pr-2 inline-flex flex-col justify-start items-start"><span class="material-symbols-outlined text-gray-400 text-lg">person</span></div>
                        <div class="flex-1 pt-px pb-0.5 inline-flex flex-col justify-start items-start overflow-hidden">
                            <div class="self-stretch flex flex-col justify-start items-start overflow-hidden">
                                <div
                                    class="self-stretch justify-center text-gray-500/60 text-sm font-normal font-['Inter']">
                                    Nabil</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="self-stretch flex flex-col justify-start items-start gap-1">
                    <div class="self-stretch flex flex-col justify-start items-start">
                        <div class="self-stretch justify-center"><span
                                class="text-gray-700 text-xs font-semibold font-['Inter'] leading-4 tracking-tight">Nomor
                                WhatsApp </span><span
                                class="text-red-700 text-xs font-semibold font-['Inter'] leading-4 tracking-tight">*</span>
                        </div>
                    </div>
                    <div
                        class="self-stretch h-11 px-3 bg-purple-50 rounded-xl outline outline-1 outline-offset-[-1px] outline-slate-300/60 inline-flex justify-start items-center">
                        <div class="size- pr-2.5 inline-flex flex-col justify-start items-start">
                            <div
                                class="size- pr-2.5 border-r border-slate-300/60 inline-flex justify-start items-center gap-1">
                                <div class="size- inline-flex flex-col justify-start items-start"><span class="material-symbols-outlined text-gray-400 text-lg">chat</span></div>
                                <div class="size- inline-flex flex-col justify-start items-start">
                                    <div
                                        class="justify-center text-slate-900 text-xs font-bold font-['Inter'] leading-4 tracking-tight">
                                        +62</div>
                                </div>
                            </div>
                        </div>
                        <div class="flex-1 pt-px pb-0.5 inline-flex flex-col justify-start items-start overflow-hidden">
                            <div class="self-stretch flex flex-col justify-start items-start overflow-hidden">
                                <div
                                    class="self-stretch justify-center text-gray-500/60 text-sm font-normal font-['Inter']">
                                    812-3456-7890</div>
                            </div>
                        </div>
                    </div>
                    <div class="self-stretch pt-0.5 inline-flex justify-start items-start gap-1">
                        <div class="size- pt-0.5 inline-flex flex-col justify-start items-start"><span class="material-symbols-outlined text-sky-700 text-sm">info</span></div>
                        <div class="size- pr-2 inline-flex flex-col justify-start items-start">
                            <div class="justify-center text-gray-500 text-xs font-normal font-['Inter'] leading-4">
                                Penting untuk konfirmasi pengantaran kurir & update<br />status pengiriman.</div>
                        </div>
                    </div>
                </div>
                <div class="self-stretch flex flex-col justify-start items-start gap-1">
                    <div class="self-stretch flex flex-col justify-start items-start">
                        <div class="self-stretch justify-center"><span
                                class="text-gray-700 text-xs font-semibold font-['Inter'] leading-4 tracking-tight">Alamat
                                Email </span><span
                                class="text-red-700 text-xs font-semibold font-['Inter'] leading-4 tracking-tight">*</span>
                        </div>
                    </div>
                    <div
                        class="self-stretch h-11 px-3 bg-purple-50 rounded-xl outline outline-1 outline-offset-[-1px] outline-slate-300/60 inline-flex justify-start items-center">
                        <div class="size- pr-2 inline-flex flex-col justify-start items-start"><span class="material-symbols-outlined text-gray-400 text-lg">mail</span></div>
                        <div class="flex-1 pt-px pb-0.5 inline-flex flex-col justify-start items-start overflow-hidden">
                            <div class="self-stretch flex flex-col justify-start items-start overflow-hidden">
                                <div
                                    class="self-stretch justify-center text-gray-500/60 text-sm font-normal font-['Inter']">
                                    nabil@gmail.com</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="self-stretch flex flex-col justify-start items-start gap-1">
                    <div class="self-stretch flex flex-col justify-start items-start">
                        <div class="self-stretch justify-center"><span
                                class="text-gray-700 text-xs font-semibold font-['Inter'] leading-4 tracking-tight">Kata
                                Sandi </span><span
                                class="text-red-700 text-xs font-semibold font-['Inter'] leading-4 tracking-tight">*</span>
                        </div>
                    </div>
                    <div
                        class="self-stretch h-11 px-3 bg-purple-50 rounded-xl outline outline-1 outline-offset-[-1px] outline-slate-300/60 inline-flex justify-start items-center">
                        <div class="size- pr-2 inline-flex flex-col justify-start items-start"><span class="material-symbols-outlined text-gray-400 text-lg">lock</span></div>
                        <div class="flex-1 pt-px pb-0.5 inline-flex flex-col justify-start items-start overflow-hidden">
                            <div class="self-stretch flex flex-col justify-start items-start overflow-hidden">
                                <div
                                    class="self-stretch justify-center text-gray-500/60 text-sm font-normal font-['Inter']">
                                    nabil24</div>
                            </div>
                        </div>
                        <div class="size- px-1 inline-flex flex-col justify-center items-center cursor-pointer"><span class="material-symbols-outlined text-gray-400 text-lg">visibility</span></div>
                    </div>
                </div>
                <div class="self-stretch pt-1 pb-2 inline-flex justify-start items-start gap-2.5">
                    <div class="size-4 relative">
                        <div class="size- left-[-1px] top-[1px] absolute inline-flex justify-start items-center">
                            <div
                                class="size-4 bg-blue-800 rounded-sm outline outline-1 outline-offset-[-1px] outline-black/0 inline-flex flex-col justify-center items-center overflow-hidden">
                                <div class="size-4 relative overflow-hidden">
                                    <div class="w-2 h-1.5 left-[3.51px] top-[4.50px] absolute bg-white"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="w-72 h-8 relative">
                        <div class="left-0 top-[-0.75px] absolute justify-center"><span
                                class="text-gray-700 text-xs font-normal font-['Inter'] leading-4">Saya menyetujui
                            </span><span
                                class="text-blue-800 text-xs font-semibold font-['Inter'] underline leading-4">syarat
                                pemesanan</span><span
                                class="text-gray-700 text-xs font-normal font-['Inter'] leading-4"> dan </span><span
                                class="text-blue-800 text-xs font-semibold font-['Inter'] underline leading-4">privasi</span><span
                                class="text-gray-700 text-xs font-normal font-['Inter'] leading-4"><br />Kopi Tiga
                                Titik.</span></div>
                    </div>
                </div>
                <div
                    class="self-stretch h-12 relative bg-blue-800 rounded-xl inline-flex justify-center items-center gap-2">
                    <div
                        class="w-80 h-12 left-0 top-0 absolute bg-white/0 rounded-xl shadow-[0px_2px_4px_-2px_rgba(0,74,173,0.20)] shadow-[0px_4px_6px_-1px_rgba(0,74,173,0.20)]">
                    </div>
                    <div class="size- inline-flex flex-col justify-start items-center">
                        <div
                            class="text-center justify-center text-white text-sm font-semibold font-['Inter'] leading-5 tracking-tight">
                            Daftar Akun Sekarang</div>
                    </div>
                    <div class="size- inline-flex flex-col justify-start items-center"><span class="material-symbols-outlined text-white text-base">arrow_forward</span></div>
                </div>
            </div>
            <div class="self-stretch pt-7 flex flex-col justify-start items-start">
                <div class="self-stretch py-[0.50px] inline-flex justify-center items-start">
                    <div class="text-center justify-center text-gray-700 text-sm font-normal font-['Inter'] leading-5">
                        Sudah punya akun? </div>
                    <div
                        class="text-center justify-center text-blue-800 text-sm font-bold font-['Plus_Jakarta_Sans'] leading-5">
                        Masuk di sini</div>
                </div>
            </div>
            <div class="self-stretch pt-5 flex flex-col justify-start items-start">
                <div class="self-stretch pt-3.5 pb-3 flex flex-col justify-start items-center">
                    <div class="size- inline-flex justify-start items-center gap-1.5">
                        <div class="size- inline-flex flex-col justify-start items-center"><span class="material-symbols-outlined text-gray-400 text-sm">verified_user</span></div>
                        <div class="size- inline-flex flex-col justify-start items-center">
                            <div
                                class="text-center justify-center text-gray-500 text-[10px] font-bold font-['Inter'] leading-4 tracking-tight">
                                Keamanan data terjamin • Kopi Tiga Titik Mojokerto</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
