<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Profil Basarnas - Badan SAR Nasional</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        colors: {
                            primary: '#F3A953',
                            secondary: '#064ACB',
                            background: '#F0F0F0',
                        },
                        fontFamily: {
                            poppins: ['Poppins', 'sans-serif'],
                        }
                    }
                }
            }
        </script>
        <style>
            body {
                font-family: 'Poppins', sans-serif;
            }
            .bg-hero {
                background: #064ACB;
            }
            .text-shadow {
                text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            }
        </style>
    </head>
    <body class="bg-background font-poppins">
        <!-- Top Navigation -->
        <nav class="bg-white shadow-lg relative z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-4">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center p-1 border-2 border-secondary">
                            <img src="https://upload.wikimedia.org/wikipedia/id/5/5a/Logo_Basarnas.png" alt="Logo BASARNAS" class="w-full h-full object-contain" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="w-full h-full bg-secondary rounded-full items-center justify-center hidden">
                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                                </svg>
                            </div>
                        </div>
                        <div>
                            <h1 class="text-xl font-bold text-secondary">BASARNAS</h1>
                            <p class="text-sm text-gray-600">Badan SAR Nasional</p>
                        </div>
                    </div>
                    
                    <!-- Login Button -->
                    <div class="flex items-center space-x-4">
                        <a href="/admin/login" class="bg-secondary hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition duration-300 shadow-lg hover:shadow-xl">
                            Login
                        </a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero/Jumbotron Section -->
        <section class="bg-hero relative overflow-hidden">
            <div class="absolute inset-0">
                <div class="absolute inset-0 bg-black opacity-20"></div>
                <div class="absolute inset-0 bg-secondary/80"></div>
            </div>
            
            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 lg:py-32">
                <div class="text-center">
                    <div class="mb-8">
                        <div class="w-24 h-24 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center mx-auto mb-6 p-3 border-2 border-white/30">
                            <img src="https://upload.wikimedia.org/wikipedia/id/5/5a/Logo_Basarnas.png" alt="Logo BASARNAS" class="w-full h-full object-contain" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="w-full h-full items-center justify-center hidden">
                                <svg class="w-12 h-12 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                    
                    <h1 class="text-5xl lg:text-7xl font-bold text-white mb-6 text-shadow">
                        BASARNAS
                    </h1>
                    <h2 class="text-2xl lg:text-3xl font-semibold text-white/90 mb-8 text-shadow">
                        Badan Search and Rescue Nasional
                    </h2>
                    <p class="text-xl lg:text-2xl text-white/80 max-w-4xl mx-auto mb-12 text-shadow">
                        Siap Menolong 24/7 - Melindungi Jiwa Manusia di Darat, Laut, dan Udara Indonesia
                    </p>
                    
                    <!-- Hero Stats -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6 max-w-4xl mx-auto">
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-6 text-center">
                            <div class="text-3xl font-bold text-white">24/7</div>
                            <div class="text-white/80">Siaga Operasi</div>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-6 text-center">
                            <div class="text-3xl font-bold text-white">34</div>
                            <div class="text-white/80">Kantor SAR</div>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-6 text-center">
                            <div class="text-3xl font-bold text-white">1000+</div>
                            <div class="text-white/80">Personel</div>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-6 text-center">
                            <div class="text-3xl font-bold text-white">50K+</div>
                            <div class="text-white/80">Korban Tertolong</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Wave decoration -->
            <div class="absolute bottom-0 left-0 right-0">
                <svg viewBox="0 0 1200 120" preserveAspectRatio="none" class="w-full h-12 fill-background">
                    <path d="M0,0V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5C438.64,32.43,512.34,53.67,583,72.05c69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,52.47V0Z"></path>
                </svg>
            </div>
        </section>

        <!-- Profile Content -->
        <section class="py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <!-- About Section -->
                <div class="mb-20">
                    <div class="text-center mb-12">
                        <h2 class="text-4xl font-bold text-secondary mb-4">Tentang BASARNAS</h2>
                        <div class="w-24 h-1 bg-primary mx-auto mb-6"></div>
                        <p class="text-lg text-gray-700 max-w-3xl mx-auto">
                            Badan SAR Nasional adalah lembaga pemerintah non-kementerian yang bertugas melaksanakan tugas pemerintahan di bidang pencarian dan pertolongan
                        </p>
                    </div>

                    <div class="grid lg:grid-cols-2 gap-12 items-center">
                        <div class="space-y-6">
                            <div class="bg-white rounded-2xl shadow-xl p-8">
                                <div class="flex items-center mb-4">
                                    <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center mr-4">
                                        <svg class="w-6 h-6 text-primary" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                        </svg>
                                    </div>
                                    <h3 class="text-2xl font-bold text-secondary">Visi</h3>
                                </div>
                                <p class="text-gray-700">
                                    Menjadi organisasi SAR yang unggul dan terpercaya dalam melaksanakan operasi pencarian dan pertolongan untuk keselamatan jiwa manusia.
                                </p>
                            </div>

                            <div class="bg-white rounded-2xl shadow-xl p-8">
                                <div class="flex items-center mb-4">
                                    <div class="w-12 h-12 bg-secondary/10 rounded-lg flex items-center justify-center mr-4">
                                        <svg class="w-6 h-6 text-secondary" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <h3 class="text-2xl font-bold text-secondary">Misi</h3>
                                </div>
                                <ul class="text-gray-700 space-y-2">
                                    <li class="flex items-start">
                                        <span class="w-2 h-2 bg-primary rounded-full mt-2 mr-3 flex-shrink-0"></span>
                                        Melaksanakan operasi SAR yang profesional, efektif dan efisien
                                    </li>
                                    <li class="flex items-start">
                                        <span class="w-2 h-2 bg-primary rounded-full mt-2 mr-3 flex-shrink-0"></span>
                                        Mengembangkan kemampuan dan kapasitas SAR nasional
                                    </li>
                                    <li class="flex items-start">
                                        <span class="w-2 h-2 bg-primary rounded-full mt-2 mr-3 flex-shrink-0"></span>
                                        Membangun kerjasama SAR tingkat nasional dan internasional
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="space-y-6">
                            <div class="bg-gradient-to-br from-secondary to-secondary rounded-2xl p-8 text-white">
                                <h3 class="text-2xl font-bold mb-6">Nilai-Nilai BASARNAS</h3>
                                <div class="space-y-4">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 bg-white/20 rounded-full flex items-center justify-center mr-4">
                                            <span class="text-sm font-bold">R</span>
                                        </div>
                                        <div>
                                            <div class="font-semibold">Responsif</div>
                                            <div class="text-sm text-white/80">Tanggap dan cepat dalam memberikan respon</div>
                                        </div>
                                    </div>
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 bg-white/20 rounded-full flex items-center justify-center mr-4">
                                            <span class="text-sm font-bold">P</span>
                                        </div>
                                        <div>
                                            <div class="font-semibold">Profesional</div>
                                            <div class="text-sm text-white/80">Bekerja dengan keahlian dan kompeten</div>
                                        </div>
                                    </div>
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 bg-white/20 rounded-full flex items-center justify-center mr-4">
                                            <span class="text-sm font-bold">H</span>
                                        </div>
                                        <div>
                                            <div class="font-semibold">Humanity</div>
                                            <div class="text-sm text-white/80">Mengutamakan nilai-nilai kemanusiaan</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white rounded-2xl shadow-xl p-8">
                                <h3 class="text-2xl font-bold text-secondary mb-4">Sejarah Singkat</h3>
                                <p class="text-gray-700 mb-4">
                                    BASARNAS dibentuk berdasarkan Keputusan Presiden Republik Indonesia Nomor 15 Tahun 2001 tentang Badan SAR Nasional.
                                </p>
                                <p class="text-gray-700">
                                    Sebagai lembaga yang bertanggung jawab dalam koordinasi dan pelaksanaan operasi pencarian dan pertolongan di seluruh wilayah Indonesia.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Services Section -->
                <div class="mb-20">
                    <div class="text-center mb-12">
                        <h2 class="text-4xl font-bold text-secondary mb-4">Layanan SAR</h2>
                        <div class="w-24 h-1 bg-primary mx-auto mb-6"></div>
                        <p class="text-lg text-gray-700 max-w-3xl mx-auto">
                            Melayani berbagai jenis operasi pencarian dan pertolongan di seluruh Indonesia
                        </p>
                    </div>

                    <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                        <div class="bg-white rounded-2xl shadow-xl p-8 text-center hover:shadow-2xl transition-all duration-300 hover:-translate-y-2">
                            <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-6">
                                <svg class="w-8 h-8 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M21 16v-2l-8-5V3.5c0-.83-.67-1.5-1.5-1.5S10 2.67 10 3.5V9l-8 5v2l8-2.5V19l-2 1.5V22l3.5-1 3.5 1v-1.5L13 19v-5.5l8 2.5z"/>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-secondary mb-3">SAR Udara</h3>
                            <p class="text-gray-600">Operasi pencarian dan pertolongan menggunakan pesawat dan helikopter</p>
                        </div>

                        <div class="bg-white rounded-2xl shadow-xl p-8 text-center hover:shadow-2xl transition-all duration-300 hover:-translate-y-2">
                            <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-6">
                                <svg class="w-8 h-8 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M6.5 12C5.11 12 4 13.11 4 14.5S5.11 17 6.5 17 9 15.89 9 14.5 7.89 12 6.5 12zm0 3c-.28 0-.5-.22-.5-.5s.22-.5.5-.5.5.22.5.5-.22.5-.5.5zM17.5 12c-1.39 0-2.5 1.11-2.5 2.5s1.11 2.5 2.5 2.5 2.5-1.11 2.5-2.5-1.11-2.5-2.5-2.5zm0 3c-.28 0-.5-.22-.5-.5s.22-.5.5-.5.5.22.5.5-.22.5-.5.5zM7 24l1.5-3h7l1.5 3H7zM12 6c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2z"/>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-secondary mb-3">SAR Laut</h3>
                            <p class="text-gray-600">Operasi pencarian dan pertolongan di wilayah perairan dan pantai</p>
                        </div>

                        <div class="bg-white rounded-2xl shadow-xl p-8 text-center hover:shadow-2xl transition-all duration-300 hover:-translate-y-2">
                            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                                <svg class="w-8 h-8 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M14,6L10.25,11L13.1,14.8L11.5,16C9.81,13.75 7,10 7,10L1,18H23L14,6Z"/>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-secondary mb-3">SAR Darat</h3>
                            <p class="text-gray-600">Operasi pencarian dan pertolongan di wilayah daratan dan pegunungan</p>
                        </div>

                        <div class="bg-white rounded-2xl shadow-xl p-8 text-center hover:shadow-2xl transition-all duration-300 hover:-translate-y-2">
                            <div class="w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-6">
                                <svg class="w-8 h-8 text-orange-600" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12,11.5A2.5,2.5 0 0,1 9.5,9A2.5,2.5 0 0,1 12,6.5A2.5,2.5 0 0,1 14.5,9A2.5,2.5 0 0,1 12,11.5M12,2A7,7 0 0,0 5,9C5,14.25 12,22 12,22C12,22 19,14.25 19,9A7,7 0 0,0 12,2Z"/>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-secondary mb-3">SAR Perkotaan</h3>
                            <p class="text-gray-600">Operasi pencarian dan pertolongan di wilayah perkotaan dan bencana</p>
                        </div>
                    </div>
                </div>

                <!-- Emergency Contact -->
                <div class="mb-20">
                    <div class="bg-red-600 rounded-2xl p-8 md:p-12 text-center text-white">
                        <div class="max-w-3xl mx-auto">
                            <div class="w-20 h-20 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-6">
                                <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M6.62,10.79C8.06,13.62 10.38,15.94 13.21,17.38L15.41,15.18C15.69,14.9 16.08,14.82 16.43,14.93C17.55,15.3 18.75,15.5 20,15.5A1,1 0 0,1 21,16.5V20A1,1 0 0,1 20,21A17,17 0 0,1 3,4A1,1 0 0,1 4,3H7.5A1,1 0 0,1 8.5,4C8.5,5.25 8.7,6.45 9.07,7.57C9.18,7.92 9.1,8.31 8.82,8.59L6.62,10.79Z"/>
                                </svg>
                            </div>
                            <h2 class="text-3xl font-bold mb-4">Darurat SAR</h2>
                            <p class="text-xl mb-6">Hubungi segera jika membutuhkan bantuan pencarian dan pertolongan</p>
                            <div class="text-4xl font-bold mb-2">115</div>
                            <p class="text-lg opacity-90">Hotline 24 Jam - Gratis dari seluruh Indonesia</p>
                        </div>
                    </div>
                </div>

                <!-- Social Media Section -->
                <div class="mb-20">
                    <div class="text-center mb-12">
                        <h2 class="text-4xl font-bold text-secondary mb-4">Ikuti Media Sosial Kami</h2>
                        <div class="w-24 h-1 bg-primary mx-auto mb-6"></div>
                        <p class="text-lg text-gray-700 max-w-3xl mx-auto">
                            Dapatkan informasi terbaru dan tips keselamatan dari BASARNAS
                        </p>
                    </div>

                    <div class="flex flex-wrap justify-center gap-6">
                        <a href="https://www.instagram.com/basarnas_ri/" target="_blank" class="bg-gradient-to-r from-purple-500 to-pink-500 text-white p-6 rounded-2xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 flex items-center space-x-4">
                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                            </svg>
                            <span class="font-semibold">@basarnas_ri</span>
                        </a>

                        <a href="https://twitter.com/basarnas" target="_blank" class="bg-gradient-to-r from-blue-400 to-blue-600 text-white p-6 rounded-2xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 flex items-center space-x-4">
                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                            </svg>
                            <span class="font-semibold">@basarnas</span>
                        </a>

                        <a href="https://www.facebook.com/basarnasri" target="_blank" class="bg-gradient-to-r from-blue-600 to-blue-800 text-white p-6 rounded-2xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 flex items-center space-x-4">
                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                            <span class="font-semibold">BASARNAS RI</span>
                        </a>

                        <a href="https://www.youtube.com/channel/basarnas" target="_blank" class="bg-gradient-to-r from-red-500 to-red-700 text-white p-6 rounded-2xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 flex items-center space-x-4">
                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                            </svg>
                            <span class="font-semibold">BASARNAS Official</span>
                        </a>

                        <a href="https://wa.me/6281222000115" target="_blank" class="bg-gradient-to-r from-green-500 to-green-600 text-white p-6 rounded-2xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 flex items-center space-x-4">
                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.890-5.335 11.893-11.893A11.821 11.821 0 0020.484 3.488z"/>
                            </svg>
                            <span class="font-semibold">WhatsApp SAR</span>
                        </a>
                    </div>
                </div>

                <!-- Organization Structure -->
                <div class="mb-20">
                    <div class="text-center mb-12">
                        <h2 class="text-4xl font-bold text-secondary mb-4">Struktur Organisasi</h2>
                        <div class="w-24 h-1 bg-primary mx-auto mb-6"></div>
                        <p class="text-lg text-gray-700 max-w-3xl mx-auto">
                            BASARNAS memiliki struktur organisasi yang tersebar di seluruh Indonesia
                        </p>
                    </div>

                    <div class="grid md:grid-cols-3 gap-8">
                        <div class="bg-white rounded-2xl shadow-xl p-8 text-center">
                            <div class="w-16 h-16 bg-secondary/10 rounded-full flex items-center justify-center mx-auto mb-6">
                                <svg class="w-8 h-8 text-secondary" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                </svg>
                            </div>
                            <h3 class="text-2xl font-bold text-secondary mb-3">Kantor Pusat</h3>
                            <p class="text-gray-600 mb-4">Koordinasi nasional dan kebijakan operasional SAR</p>
                            <div class="text-sm text-gray-500">Jakarta</div>
                        </div>

                        <div class="bg-white rounded-2xl shadow-xl p-8 text-center">
                            <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-6">
                                <svg class="w-8 h-8 text-primary" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                </svg>
                            </div>
                            <h3 class="text-2xl font-bold text-secondary mb-3">34 Kantor SAR</h3>
                            <p class="text-gray-600 mb-4">Kantor SAR regional di seluruh Indonesia</p>
                            <div class="text-sm text-gray-500">Nasional</div>
                        </div>

                        <div class="bg-white rounded-2xl shadow-xl p-8 text-center">
                            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                                <svg class="w-8 h-8 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M16 4c0-1.11.89-2 2-2s2 .89 2 2-.89 2-2 2-2-.89-2-2zm4 18v-6h2.5l-2.54-7.63A1.5 1.5 0 0 0 18.5 8H17c-.8 0-1.5.7-1.5 1.5v7c0 .8.7 1.5 1.5 1.5h1v4h2z"/>
                                </svg>
                            </div>
                            <h3 class="text-2xl font-bold text-secondary mb-3">Pos SAR</h3>
                            <p class="text-gray-600 mb-4">Pos SAR di lokasi strategis dan rawan bencana</p>
                            <div class="text-sm text-gray-500">100+ Lokasi</div>
                        </div>
                    </div>
                </div>

                <!-- Achievement Section -->
                <div class="mb-20">
                    <div class="bg-secondary text-white py-12">
                        <div class="text-center mb-12">
                            <h2 class="text-4xl font-bold mb-4">Pencapaian BASARNAS</h2>
                            <div class="w-24 h-1 bg-white/30 mx-auto mb-6"></div>
                            <p class="text-xl opacity-90 max-w-3xl mx-auto">
                                Dedikasi dalam menyelamatkan jiwa dan membantu masyarakat Indonesia
                            </p>
                        </div>

                        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                            <div class="text-center bg-white/10 backdrop-blur-sm rounded-xl p-6">
                                <div class="text-4xl font-bold mb-2">50,000+</div>
                                <div class="text-white/80">Korban Tertolong</div>
                            </div>
                            <div class="text-center bg-white/10 backdrop-blur-sm rounded-xl p-6">
                                <div class="text-4xl font-bold mb-2">5,000+</div>
                                <div class="text-white/80">Operasi SAR</div>
                            </div>
                            <div class="text-center bg-white/10 backdrop-blur-sm rounded-xl p-6">
                                <div class="text-4xl font-bold mb-2">98%</div>
                                <div class="text-white/80">Tingkat Keberhasilan</div>
                            </div>
                            <div class="text-center bg-white/10 backdrop-blur-sm rounded-xl p-6">
                                <div class="text-4xl font-bold mb-2">20+</div>
                                <div class="text-white/80">Tahun Pengabdian</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="mb-20">
                    <div class="text-center mb-12">
                        <h2 class="text-4xl font-bold text-secondary mb-4">Kontak & Informasi</h2>
                        <div class="w-24 h-1 bg-primary mx-auto mb-6"></div>
                        <p class="text-lg text-gray-700 max-w-3xl mx-auto">
                            Hubungi kami untuk informasi lebih lanjut atau dalam keadaan darurat
                        </p>
                    </div>

                    <div class="grid md:grid-cols-3 gap-8">
                        <div class="bg-white rounded-2xl shadow-xl p-8">
                            <div class="flex items-center mb-6">
                                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center mr-4">
                                    <svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M6.62,10.79C8.06,13.62 10.38,15.94 13.21,17.38L15.41,15.18C15.69,14.9 16.08,14.82 16.43,14.93C17.55,15.3 18.75,15.5 20,15.5A1,1 0 0,1 21,16.5V20A1,1 0 0,1 20,21A17,17 0 0,1 3,4A1,1 0 0,1 4,3H7.5A1,1 0 0,1 8.5,4C8.5,5.25 8.7,6.45 9.07,7.57C9.18,7.92 9.1,8.31 8.82,8.59L6.62,10.79Z"/>
                                    </svg>
                                </div>
                                <h3 class="text-xl font-bold text-secondary">Darurat SAR</h3>
                            </div>
                            <div class="space-y-2">
                                <div class="text-3xl font-bold text-red-600">115</div>
                                <div class="text-gray-600">Hotline 24 Jam</div>
                                <div class="text-sm text-gray-500">Gratis dari seluruh Indonesia</div>
                            </div>
                        </div>

                        <div class="bg-white rounded-2xl shadow-xl p-8">
                            <div class="flex items-center mb-6">
                                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                                    <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                                    </svg>
                                </div>
                                <h3 class="text-xl font-bold text-secondary">Kantor Pusat</h3>
                            </div>
                            <div class="space-y-2">
                                <div class="text-gray-700 font-medium">BASARNAS</div>
                                <div class="text-gray-600">Jl. Angkasa Raya No. 2</div>
                                <div class="text-gray-600">Kemayoran, Jakarta Pusat</div>
                                <div class="text-gray-600">DKI Jakarta 10610</div>
                            </div>
                        </div>

                        <div class="bg-white rounded-2xl shadow-xl p-8">
                            <div class="flex items-center mb-6">
                                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mr-4">
                                    <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M20,4H4C2.89,4 2,4.89 2,6V18A2,2 0 0,0 4,20H20A2,2 0 0,0 22,18V6C22,4.89 21.1,4 20,4M20,8L12,13L4,8V6L12,11L20,6V8Z"/>
                                    </svg>
                                </div>
                                <h3 class="text-xl font-bold text-secondary">Email & Web</h3>
                            </div>
                            <div class="space-y-2">
                                <div class="text-gray-700">info@basarnas.go.id</div>
                                <div class="text-gray-700">www.basarnas.go.id</div>
                                <div class="text-gray-600 text-sm">Informasi umum dan layanan</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-secondary text-white py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid md:grid-cols-3 gap-8 mb-8">
                    <!-- Logo & Info -->
                    <div class="flex flex-col items-center md:items-start">
                        <div class="flex items-center space-x-4 mb-4">
                            <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center p-2 border-2 border-primary">
                                <img src="https://upload.wikimedia.org/wikipedia/id/5/5a/Logo_Basarnas.png" alt="Logo BASARNAS" class="w-full h-full object-contain" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="w-full h-full bg-primary rounded-full items-center justify-center hidden">
                                    <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                                    </svg>
                                </div>
                            </div>
                            <div>
                                <div class="text-2xl font-bold">BASARNAS</div>
                                <div class="text-white/80">Badan SAR Nasional</div>
                            </div>
                        </div>
                        <div class="text-white/60 text-sm text-center md:text-left max-w-sm">
                            Siap Menolong 24/7 - Melindungi Jiwa Manusia di Darat, Laut, dan Udara Indonesia
                        </div>
                    </div>

                    <!-- Contact Info -->
                    <div class="text-center md:text-left">
                        <h3 class="text-lg font-semibold mb-4">Kontak Darurat</h3>
                        <div class="space-y-3">
                            <div class="flex items-center justify-center md:justify-start">
                                <svg class="w-5 h-5 mr-3 text-primary" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M6.62,10.79C8.06,13.62 10.38,15.94 13.21,17.38L15.41,15.18C15.69,14.9 16.08,14.82 16.43,14.93C17.55,15.3 18.75,15.5 20,15.5A1,1 0 0,1 21,16.5V20A1,1 0 0,1 20,21A17,17 0 0,1 3,4A1,1 0 0,1 4,3H7.5A1,1 0 0,1 8.5,4C8.5,5.25 8.7,6.45 9.07,7.57C9.18,7.92 9.1,8.31 8.82,8.59L6.62,10.79Z"/>
                                </svg>
                                <span class="text-2xl font-bold text-primary">115</span>
                            </div>
                            <div class="text-white/80 text-sm">Hotline 24 Jam - Gratis</div>
                            <div class="flex items-center justify-center md:justify-start mt-2">
                                <svg class="w-5 h-5 mr-3 text-white/60" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M20,4H4C2.89,4 2,4.89 2,6V18A2,2 0 0,0 4,20H20A2,2 0 0,0 22,18V6C22,4.89 21.1,4 20,4M20,8L12,13L4,8V6L12,11L20,6V8Z"/>
                                </svg>
                                <span class="text-white/80 text-sm">info@basarnas.go.id</span>
                            </div>
                        </div>
                    </div>

                    <!-- Social Media -->
                    <div class="text-center md:text-left">
                        <h3 class="text-lg font-semibold mb-4">Ikuti Kami</h3>
                        <div class="flex flex-wrap justify-center md:justify-start gap-3">
                            <a href="https://www.instagram.com/basarnas_ri/" target="_blank" class="w-10 h-10 bg-purple-600 hover:bg-purple-700 rounded-full flex items-center justify-center transition-colors duration-200">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                                </svg>
                            </a>
                            
                            <a href="https://twitter.com/basarnas" target="_blank" class="w-10 h-10 bg-blue-500 hover:bg-blue-600 rounded-full flex items-center justify-center transition-colors duration-200">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                                </svg>
                            </a>

                            <a href="https://www.facebook.com/basarnasri" target="_blank" class="w-10 h-10 bg-blue-700 hover:bg-blue-800 rounded-full flex items-center justify-center transition-colors duration-200">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                </svg>
                            </a>

                            <a href="https://www.youtube.com/channel/basarnas" target="_blank" class="w-10 h-10 bg-red-600 hover:bg-red-700 rounded-full flex items-center justify-center transition-colors duration-200">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                                </svg>
                            </a>

                            <a href="https://wa.me/6281222000115" target="_blank" class="w-10 h-10 bg-green-600 hover:bg-green-700 rounded-full flex items-center justify-center transition-colors duration-200">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.890-5.335 11.893-11.893A11.821 11.821 0 0020.484 3.488z"/>
                                </svg>
                            </a>
                            
                            <a href="https://www.linkedin.com/company/basarnas" target="_blank" class="w-10 h-10 bg-blue-600 hover:bg-blue-700 rounded-full flex items-center justify-center transition-colors duration-200">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                                </svg>
                            </a>
                        </div>
                        
                        <div class="mt-4 text-center md:text-left">
                            <div class="text-white/60 text-sm">Dapatkan informasi terbaru dan</div>
                            <div class="text-white/60 text-sm">tips keselamatan dari BASARNAS</div>
                        </div>
                    </div>
                </div>
                
                <div class="border-t border-white/20 pt-8">
                    <div class="text-center">
                        <div class="text-white/80 mb-1">© 2025 Badan SAR Nasional Republik Indonesia</div>
                        <div class="text-white/60 text-sm mb-4">
                            Website resmi Badan Search and Rescue Nasional RI
                        </div>
                    </div>
                </div>
            </div>
        </footer>

        <script>
            // Smooth scrolling for better UX
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    document.querySelector(this.getAttribute('href')).scrollIntoView({
                        behavior: 'smooth'
                    });
                });
            });

            // Add scroll effect to navbar
            window.addEventListener('scroll', function() {
                const nav = document.querySelector('nav');
                if (window.scrollY > 100) {
                    nav.classList.add('shadow-2xl');
                } else {
                    nav.classList.remove('shadow-2xl');
                }
            });
        </script>
    </body>
</html>