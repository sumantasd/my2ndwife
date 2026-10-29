@php
    $restaurantName = \App\Models\RestaurantSetting::get('restaurant_name', 'My 2nd Wife');
    $restaurantPhone = \App\Models\RestaurantSetting::get('restaurant_phone', '+91 98300 12345');
    $restaurantEmail = \App\Models\RestaurantSetting::get('restaurant_email', 'hello@my2ndwife.com');
    $restaurantAddress = \App\Models\RestaurantSetting::get('restaurant_address', 'Plot 42, Park Street Boulevard, Park Circus, Kolkata - 700016');
    $openTime = \App\Models\RestaurantSetting::get('opening_time', '11:00 AM');
    $closeTime = \App\Models\RestaurantSetting::get('closing_time', '11:30 PM');
    $openingHours = "{$openTime} - {$closeTime}";
    
    $officialLogo = \App\Models\RestaurantSetting::get('restaurant_logo_dark') 
        ?: \App\Models\RestaurantSetting::get('restaurant_logo', 'images/defaults/logo.svg');
    $footerLogo = asset($officialLogo);

    $footerDesc = \App\Models\RestaurantSetting::get('footer_description') ?: (\App\Models\RestaurantSetting::get('restaurant_tagline', 'Flavors You Can Never Resist.') . ' ' . \App\Models\RestaurantSetting::get('restaurant_short_description', 'Authentic Indian & Bengali culinary delicacies delivered piping hot.'));
    
    $fb = \App\Models\RestaurantSetting::get('facebook_url');
    $fbEnabled = (bool) \App\Models\RestaurantSetting::get('facebook_enabled', true);

    $insta = \App\Models\RestaurantSetting::get('instagram_url');
    $instaEnabled = (bool) \App\Models\RestaurantSetting::get('instagram_enabled', true);

    $tw = \App\Models\RestaurantSetting::get('twitter_url');
    $twEnabled = (bool) \App\Models\RestaurantSetting::get('twitter_enabled', true);

    $yt = \App\Models\RestaurantSetting::get('youtube_url');
    $ytEnabled = (bool) \App\Models\RestaurantSetting::get('youtube_enabled', true);

    $linkedin = \App\Models\RestaurantSetting::get('linkedin_url');
    $liEnabled = (bool) \App\Models\RestaurantSetting::get('linkedin_enabled', true);

    $whatsapp = \App\Models\RestaurantSetting::get('restaurant_whatsapp', '+91 98300 12345');
    $waEnabled = (bool) \App\Models\RestaurantSetting::get('whatsapp_enabled', true);

    $isTempClosed = (bool) \App\Models\RestaurantSetting::get('is_temporarily_closed', false);
    $isKitchenOpen = (bool) \App\Models\RestaurantSetting::get('is_restaurant_open', true);
    
    $newsletterEnabled = (bool) \App\Models\RestaurantSetting::get('footer_newsletter_enabled', true);
    $newsletterHeading = \App\Models\RestaurantSetting::get('footer_newsletter_heading', 'Subscribe to our newsletter');
    $newsletterDesc = \App\Models\RestaurantSetting::get('footer_newsletter_description', 'Get the latest offers, new dishes and updates delivered to your inbox.');
    $newsletterPlaceholder = \App\Models\RestaurantSetting::get('footer_newsletter_placeholder', 'Enter your email address');
    $newsletterButtonText = \App\Models\RestaurantSetting::get('footer_newsletter_button_text', 'Subscribe');

    $appDownloadEnabled = (bool) \App\Models\RestaurantSetting::get('footer_app_download_enabled', true);
    $appHeading = \App\Models\RestaurantSetting::get('footer_app_heading', 'Order Faster with Our Mobile App');
    $appDesc = \App\Models\RestaurantSetting::get('footer_app_description', 'Get our app for a faster, smoother and better food ordering experience.');
    $appAndroidEnabled = (bool) \App\Models\RestaurantSetting::get('app_android_enabled', true);
    $appAndroidLabel = \App\Models\RestaurantSetting::get('app_android_button_label', 'Android App');
    $appAndroidUrl = \App\Models\RestaurantSetting::get('app_android_url', '');
    $appPwaEnabled = (bool) \App\Models\RestaurantSetting::get('app_pwa_enabled', true);
    $appPwaLabel = \App\Models\RestaurantSetting::get('app_pwa_button_label', 'PWA App');

    $paymentMethodsEnabled = (bool) \App\Models\RestaurantSetting::get('footer_payment_methods_enabled', true);
    $decorationsEnabled = (bool) \App\Models\RestaurantSetting::get('footer_decorations_enabled', true);
    $quickLinksEnabled = (bool) \App\Models\RestaurantSetting::get('footer_quick_links_enabled', true);
    $supportLinksEnabled = (bool) \App\Models\RestaurantSetting::get('footer_support_links_enabled', true);
    $policyLinksEnabled = (bool) \App\Models\RestaurantSetting::get('footer_policy_links_enabled', true);

    $copyrightCustom = \App\Models\RestaurantSetting::get('footer_copyright_text');
    $devCreditText = \App\Models\RestaurantSetting::get('footer_dev_credit_text', 'Tech Googly');
    $devCreditUrl = \App\Models\RestaurantSetting::get('footer_dev_credit_url', 'https://techgoogly.com/');

    \App\Models\FooterLink::ensureDefaults();
    $quickLinks = \App\Models\FooterLink::with('page')->section('quick_links')->enabled()->ordered()->get();
    $supportLinks = \App\Models\FooterLink::with('page')->section('support_links')->enabled()->ordered()->get();
    $policyLinks = \App\Models\FooterLink::with('page')->section('policy_links')->enabled()->ordered()->get();
@endphp

<footer class="footer-part relative bg-[#0D1127] text-white pt-12 sm:pt-16 mt-8 sm:mt-12 border-t border-[#1E2548] overflow-hidden"
        aria-label="Website Footer">

    @if($decorationsEnabled)
        <!-- Decorative Ambient Food Line Art Doodles & Wave (Reference Visual Accent) -->
        <div class="absolute inset-0 pointer-events-none select-none overflow-hidden z-0" aria-hidden="true">
            <!-- Organic Wave Gradient Across Bottom -->
            <svg class="absolute bottom-0 left-0 right-0 w-full h-32 sm:h-44 text-[#38082A] opacity-50 preserve-3d" 
                 viewBox="0 0 1440 220" fill="none" preserveAspectRatio="none">
                <path d="M0,96L48,112C96,128,192,160,288,165.3C384,171,480,149,576,122.7C672,96,768,64,864,64C960,64,1056,96,1152,112C1248,128,1344,128,1392,128L1440,128L1440,220L1392,220C1344,220,1248,220,1152,220C1056,220,960,220,864,220C768,220,672,220,576,220C480,220,384,220,288,220C192,220,96,220,48,220L0,220Z" 
                      fill="currentColor" fill-opacity="0.35"/>
                <path d="M0,140L60,135C120,130,240,120,360,125C480,130,600,150,720,155C840,160,960,150,1080,135C1200,120,1320,100,1380,90L1440,80L1440,220L1380,220C1320,220,1200,220,1080,220C960,220,840,220,720,220C600,220,480,220,360,220C240,220,120,220,60,220L0,220Z" 
                      fill="#4D0937" fill-opacity="0.4"/>
            </svg>

            <!-- Top Right Food Doodles: Sliced Tomato, Onion Ring, Leaf Sprigs -->
            <svg class="absolute top-2 right-4 sm:right-12 w-48 h-48 sm:w-64 sm:h-64 text-[#F50073] opacity-20" 
                 viewBox="0 0 200 200" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round">
                <!-- Tomato slice -->
                <circle cx="140" cy="65" r="42" />
                <circle cx="140" cy="65" r="32" stroke-dasharray="3 3" />
                <path d="M140,30 C140,50 145,55 160,65 C145,75 140,80 140,100 C140,80 135,75 120,65 C135,55 140,50 140,30 Z" />
                <circle cx="132" cy="55" r="3" fill="currentColor" />
                <circle cx="148" cy="55" r="3" fill="currentColor" />
                <circle cx="132" cy="75" r="3" fill="currentColor" />
                <circle cx="148" cy="75" r="3" fill="currentColor" />
                <!-- Onion Rings -->
                <ellipse cx="60" cy="130" rx="36" ry="22" transform="rotate(-25 60 130)" />
                <ellipse cx="60" cy="130" rx="26" ry="16" transform="rotate(-25 60 130)" />
                <ellipse cx="85" cy="155" rx="28" ry="16" transform="rotate(-15 85 155)" />
                <!-- Herb leaves -->
                <path d="M170,120 C185,110 195,125 185,140 C175,130 170,120 170,120 Z" />
                <path d="M185,140 L195,150" />
            </svg>

            <!-- Bottom Center-Right: Chef Hat + 'Good Food Good Mood' Cursive Script -->
            <div class="hidden md:flex flex-col items-center absolute bottom-16 right-1/4 sm:right-[28%] text-[#F50073] opacity-25">
                <!-- Chef Hat SVG -->
                <svg class="w-10 h-10 mb-1" viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 36h24v4H12zM14 36v-6c0-2-1-3-3-3a7 7 0 0 1 0-14c1 0 3 1 4 2a8 8 0 0 1 15-4c2 0 4 1 5 3a7 7 0 0 1 3 13c-2 0-3 1-3 3v6" />
                    <path d="M20 36v-6M28 36v-6" />
                </svg>
                <!-- Cursive Tagline -->
                <span class="text-xs tracking-wider font-bold italic" style="font-family: 'Brush Script MT', cursive, sans-serif;">
                    Good Food Good Mood ♡
                </span>
            </div>

            <!-- Bottom Left Leaves Outline -->
            <svg class="absolute -bottom-6 -left-6 w-36 h-36 text-[#F50073] opacity-15" viewBox="0 0 100 100" fill="none" stroke="currentColor" stroke-width="1.2">
                <path d="M20,80 Q50,30 85,20 Q60,65 20,80 Z" />
                <path d="M20,80 L80,25" />
                <path d="M40,65 L50,60" />
                <path d="M55,50 L65,45" />
                <path d="M70,35 L80,30" />
            </svg>
        </div>
    @endif

    <!-- Main Footer Content Grid -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-8 lg:gap-6 pb-12">
            
            <!-- Col 1: Brand & Newsletter (4 cols on lg) -->
            <div class="lg:col-span-4 space-y-5">
                <!-- Brand Logo: Complete Official Logo Only (Without Duplicate Brand Text) -->
                <a href="{{ route('home') }}" class="inline-block group focus:outline-none" aria-label="{{ $restaurantName }} Home">
                    <img src="{{ $footerLogo }}" 
                         alt="{{ $restaurantName }}" 
                         class="h-11 sm:h-12 w-auto max-w-[240px] sm:max-w-[280px] object-contain transition-transform group-hover:scale-[1.02]">
                </a>

                <!-- Brand Tagline / Short Description -->
                <p class="text-xs text-gray-400 leading-relaxed max-w-sm">
                    {{ $footerDesc }}
                </p>

                <!-- Social Media Circular Contrast Badges (All Visible Inline SVGs) -->
                <div class="flex items-center gap-2.5 pt-1 flex-wrap">
                    @if($fbEnabled && !empty($fb) && $fb !== '#')
                        <a href="{{ $fb }}" target="_blank" rel="noopener noreferrer" 
                           aria-label="Follow My 2nd Wife on Facebook" title="Follow My 2nd Wife on Facebook"
                           class="w-9 h-9 rounded-full bg-[#1A2040] hover:bg-[#1877F2] text-gray-200 hover:text-white flex items-center justify-center border border-white/10 transition-all duration-200 hover:scale-110 shadow-sm shrink-0">
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                        </a>
                    @endif

                    @if($instaEnabled && !empty($insta) && $insta !== '#')
                        <a href="{{ $insta }}" target="_blank" rel="noopener noreferrer" 
                           aria-label="Follow My 2nd Wife on Instagram" title="Follow My 2nd Wife on Instagram"
                           class="w-9 h-9 rounded-full bg-[#1A2040] hover:bg-[#E4405F] text-gray-200 hover:text-white flex items-center justify-center border border-white/10 transition-all duration-200 hover:scale-110 shadow-sm shrink-0">
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                            </svg>
                        </a>
                    @endif

                    @if($twEnabled && !empty($tw) && $tw !== '#')
                        <a href="{{ $tw }}" target="_blank" rel="noopener noreferrer" 
                           aria-label="Follow My 2nd Wife on Twitter / X" title="Follow My 2nd Wife on Twitter / X"
                           class="w-9 h-9 rounded-full bg-[#1A2040] hover:bg-black text-gray-200 hover:text-white flex items-center justify-center border border-white/10 transition-all duration-200 hover:scale-110 shadow-sm shrink-0">
                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                            </svg>
                        </a>
                    @endif

                    @if($ytEnabled && !empty($yt) && $yt !== '#')
                        <a href="{{ $yt }}" target="_blank" rel="noopener noreferrer" 
                           aria-label="Subscribe to My 2nd Wife on YouTube" title="Subscribe to My 2nd Wife on YouTube"
                           class="w-9 h-9 rounded-full bg-[#1A2040] hover:bg-[#FF0000] text-gray-200 hover:text-white flex items-center justify-center border border-white/10 transition-all duration-200 hover:scale-110 shadow-sm shrink-0">
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                            </svg>
                        </a>
                    @endif

                    @if($liEnabled && !empty($linkedin) && $linkedin !== '#')
                        <a href="{{ $linkedin }}" target="_blank" rel="noopener noreferrer" 
                           aria-label="Connect with My 2nd Wife on LinkedIn" title="Connect with My 2nd Wife on LinkedIn"
                           class="w-9 h-9 rounded-full bg-[#1A2040] hover:bg-[#0A66C2] text-gray-200 hover:text-white flex items-center justify-center border border-white/10 transition-all duration-200 hover:scale-110 shadow-sm shrink-0">
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/>
                            </svg>
                        </a>
                    @endif

                    @if($waEnabled && !empty($whatsapp) && $whatsapp !== '#')
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $whatsapp) }}" target="_blank" rel="noopener noreferrer" 
                           aria-label="Chat with My 2nd Wife on WhatsApp" title="Chat with My 2nd Wife on WhatsApp"
                           class="w-9 h-9 rounded-full bg-[#1A2040] hover:bg-[#25D366] text-gray-200 hover:text-white flex items-center justify-center border border-white/10 transition-all duration-200 hover:scale-110 shadow-sm shrink-0">
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                            </svg>
                        </a>
                    @endif
                </div>

                @if($newsletterEnabled)
                    <!-- Live Newsletter Subscription Box with Backend Validation -->
                    <div class="pt-2" 
                         x-data="{
                             email: '',
                             loading: false,
                             statusMessage: '',
                             statusType: '',
                             async submitNewsletter() {
                                 if (!this.email) return;
                                 this.loading = true;
                                 this.statusMessage = '';
                                 try {
                                     const res = await fetch('{{ route('newsletter.subscribe') }}', {
                                         method: 'POST',
                                         headers: {
                                             'Content-Type': 'application/json',
                                             'Accept': 'application/json',
                                             'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                         },
                                         body: JSON.stringify({ email: this.email })
                                     });
                                     const data = await res.json();
                                     if (res.ok && data.success) {
                                         this.statusType = data.already_subscribed ? 'info' : 'success';
                                         this.statusMessage = data.message;
                                         if (!data.already_subscribed) this.email = '';
                                     } else {
                                         this.statusType = 'error';
                                         this.statusMessage = data.message || 'Please enter a valid email address.';
                                     }
                                 } catch (err) {
                                     this.statusType = 'error';
                                     this.statusMessage = 'Subscription failed. Please try again.';
                                 } finally {
                                     this.loading = false;
                                 }
                             }
                         }">
                        <h4 class="text-xs sm:text-sm font-bold text-white mb-1">
                            {{ $newsletterHeading }}
                        </h4>
                        <p class="text-[11px] sm:text-xs text-gray-400 mb-3">
                            {{ $newsletterDesc }}
                        </p>

                        <form @submit.prevent="submitNewsletter" class="flex items-center gap-2 max-w-sm">
                            <div class="relative flex-1">
                                <i data-lucide="mail" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                                <input type="email" x-model="email" required placeholder="{{ $newsletterPlaceholder }}"
                                       class="w-full bg-[#181E3B] text-white text-xs pl-9 pr-3 py-2.5 rounded-xl border border-[#2B3566] focus:outline-none focus:border-[#F50073] placeholder:text-gray-400 transition-colors">
                            </div>
                            <button type="submit" :disabled="loading"
                                    class="text-xs font-bold rounded-xl px-4 py-2.5 text-white bg-[#F50073] hover:bg-[#D40060] disabled:opacity-50 transition-all shrink-0 flex items-center gap-1.5 shadow-md shadow-pink-500/20 active:scale-95">
                                <span x-text="loading ? '...' : '{{ $newsletterButtonText }}'">{{ $newsletterButtonText }}</span>
                                <span x-show="!loading">&rarr;</span>
                            </button>
                        </form>

                        <template x-if="statusMessage">
                            <div class="mt-2 text-[11px] font-semibold px-3 py-1.5 rounded-lg flex items-center gap-1.5"
                                 :class="statusType === 'success' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : (statusType === 'info' ? 'bg-sky-500/10 text-sky-400 border border-sky-500/20' : 'bg-rose-500/10 text-rose-400 border border-rose-500/20')">
                                <i data-lucide="check-circle" class="w-3.5 h-3.5" x-show="statusType === 'success'"></i>
                                <i data-lucide="info" class="w-3.5 h-3.5" x-show="statusType === 'info'"></i>
                                <i data-lucide="alert-circle" class="w-3.5 h-3.5" x-show="statusType === 'error'"></i>
                                <span x-text="statusMessage"></span>
                            </div>
                        </template>
                    </div>
                @endif
            </div>

            @if($quickLinksEnabled)
            <!-- Col 2: Quick Links (2 cols on lg) -->
            <div class="lg:col-span-2">
                <div class="mb-4">
                    <h4 class="text-sm font-bold text-white tracking-wide">Quick Links</h4>
                    <div class="w-8 h-1 bg-[#F50073] rounded-full mt-2"></div>
                </div>
                <ul class="flex flex-col gap-3 text-xs text-gray-300">
                    @forelse($quickLinks as $qLink)
                        <li>
                            <a href="{{ $qLink->final_url }}" 
                               @if($qLink->open_in_new_tab) target="_blank" rel="noopener noreferrer" @endif
                               class="hover:text-white flex items-center gap-2.5 transition-colors group">
                                <i data-lucide="{{ $qLink->icon ?: 'link' }}" class="w-3.5 h-3.5 text-gray-400 group-hover:text-[#F50073] transition-colors"></i>
                                <span>{{ $qLink->title }}</span>
                            </a>
                        </li>
                    @empty
                        <li><a href="{{ route('home') }}" class="hover:text-white">Home Page</a></li>
                        <li><a href="{{ route('menu.index') }}" class="hover:text-white">Our Full Menu</a></li>
                    @endforelse
                </ul>
            </div>
            @endif

            @if($supportLinksEnabled)
            <!-- Col 3: Help & Support (2 cols on lg) -->
            <div class="lg:col-span-2">
                <div class="mb-4">
                    <h4 class="text-sm font-bold text-white tracking-wide">Help & Support</h4>
                    <div class="w-8 h-1 bg-[#F50073] rounded-full mt-2"></div>
                </div>
                <ul class="flex flex-col gap-3 text-xs text-gray-300">
                    @forelse($supportLinks as $sLink)
                        <li>
                            <a href="{{ $sLink->final_url }}" 
                               @if($sLink->open_in_new_tab) target="_blank" rel="noopener noreferrer" @endif
                               class="hover:text-white flex items-center gap-2.5 transition-colors group">
                                <i data-lucide="{{ $sLink->icon ?: 'help-circle' }}" class="w-3.5 h-3.5 text-gray-400 group-hover:text-[#F50073] transition-colors"></i>
                                <span>{{ $sLink->title }}</span>
                            </a>
                        </li>
                    @empty
                        <li><a href="{{ route('page.faq') }}" class="hover:text-white">FAQ</a></li>
                        <li><a href="{{ route('page.terms') }}" class="hover:text-white">Terms & Conditions</a></li>
                    @endforelse
                </ul>
            </div>
            @endif

            <!-- Col 4: Contact Information (2 cols on lg) -->
            <div class="lg:col-span-2 space-y-4">
                <div class="mb-4">
                    <h4 class="text-sm font-bold text-white tracking-wide">Contact Information</h4>
                    <div class="w-8 h-1 bg-[#F50073] rounded-full mt-2"></div>
                </div>
                
                <div class="flex flex-col gap-4 text-xs">
                    <!-- Phone -->
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-full bg-[#3B1232] border border-[#541A48] flex items-center justify-center text-[#F50073] shrink-0 mt-0.5">
                            <i data-lucide="phone" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0">
                            <a href="tel:{{ preg_replace('/[^0-9+]/', '', $restaurantPhone) }}" class="font-bold text-white hover:text-[#F50073] transition-colors block text-xs truncate">
                                {{ $restaurantPhone }}
                            </a>
                            <span class="text-[11px] text-gray-400 block mt-0.5">Mon - Sun, {{ $openingHours }}</span>
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-full bg-[#3B1232] border border-[#541A48] flex items-center justify-center text-[#F50073] shrink-0 mt-0.5">
                            <i data-lucide="mail" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0">
                            <a href="mailto:{{ $restaurantEmail }}" class="font-bold text-white hover:text-[#F50073] transition-colors block text-xs truncate">
                                {{ $restaurantEmail }}
                            </a>
                            <span class="text-[11px] text-gray-400 block mt-0.5">We reply within 24 hours</span>
                        </div>
                    </div>

                    <!-- Address -->
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-full bg-[#3B1232] border border-[#541A48] flex items-center justify-center text-[#F50073] shrink-0 mt-0.5">
                            <i data-lucide="map-pin" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs text-gray-300 leading-snug">
                                {{ $restaurantAddress }}
                            </p>
                        </div>
                    </div>

                    <!-- Open Daily -->
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-full bg-[#3B1232] border border-[#541A48] flex items-center justify-center text-[#F50073] shrink-0 mt-0.5">
                            <i data-lucide="clock" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="font-bold text-white text-xs">Open Daily</p>
                            <span class="text-[11px] text-gray-400 block mt-0.5">{{ $openingHours }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Col 5: Order Faster with Our Mobile App (2 cols on lg) -->
            @if($appDownloadEnabled)
                <div class="lg:col-span-2 space-y-3">
                    <div class="mb-4">
                        <h4 class="text-sm font-bold text-white tracking-wide">{{ $appHeading }}</h4>
                        <div class="w-8 h-1 bg-[#F50073] rounded-full mt-2"></div>
                    </div>

                    <p class="text-xs text-gray-400 leading-relaxed">
                        {{ $appDesc }}
                    </p>

                    <div class="flex flex-col gap-2.5 pt-1">
                        <!-- Android / Google Play Store Button -->
                        @if($appAndroidEnabled)
                            @if(!empty($appAndroidUrl) && $appAndroidUrl !== '#')
                                <a href="{{ $appAndroidUrl }}" target="_blank" rel="noopener noreferrer" 
                                   class="flex items-center gap-3 bg-[#181E3B] hover:bg-[#202750] px-3.5 py-2.5 rounded-xl border border-white/10 transition-all group shadow-sm">
                                    <div class="w-6 h-6 shrink-0 flex items-center justify-center">
                                        <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24">
                                            <path fill="#4285F4" d="M3.609 1.814L13.792 12 3.61 22.185c-.365-.328-.61-.818-.61-1.396V3.21c0-.578.245-1.068.61-1.396z"/>
                                            <path fill="#FBBC04" d="M17.18 8.613l-3.388 3.388 3.389 3.387 3.84-2.19c.67-.383.67-1.01 0-1.393l-3.841-2.192z"/>
                                            <path fill="#EA4335" d="M3.609 1.814l10.183 10.187 3.388-3.388L5.56 2.055c-.56-.32-1.39-.08-1.951.24z"/>
                                            <path fill="#34A853" d="M13.792 12.001L3.61 22.186c.56.32 1.39.56 1.95.24l11.62-6.559-3.388-3.866z"/>
                                        </svg>
                                    </div>
                                    <div class="text-left">
                                        <p class="text-[9px] text-gray-400 uppercase font-semibold leading-tight">Download for</p>
                                        <p class="text-xs font-bold text-white group-hover:text-[#F50073] transition-colors leading-tight">{{ $appAndroidLabel }}</p>
                                    </div>
                                </a>
                            @else
                                <div class="flex items-center justify-between gap-3 bg-[#181E3B]/80 px-3.5 py-2.5 rounded-xl border border-white/10" 
                                     title="Android App listing is coming soon to Google Play Store">
                                    <div class="flex items-center gap-3">
                                        <div class="w-6 h-6 shrink-0 flex items-center justify-center">
                                            <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24">
                                                <path fill="#4285F4" d="M3.609 1.814L13.792 12 3.61 22.185c-.365-.328-.61-.818-.61-1.396V3.21c0-.578.245-1.068.61-1.396z"/>
                                                <path fill="#FBBC04" d="M17.18 8.613l-3.388 3.388 3.389 3.387 3.84-2.19c.67-.383.67-1.01 0-1.393l-3.841-2.192z"/>
                                                <path fill="#EA4335" d="M3.609 1.814l10.183 10.187 3.388-3.388L5.56 2.055c-.56-.32-1.39-.08-1.951.24z"/>
                                                <path fill="#34A853" d="M13.792 12.001L3.61 22.186c.56.32 1.39.56 1.95.24l11.62-6.559-3.388-3.866z"/>
                                            </svg>
                                        </div>
                                        <div class="text-left">
                                            <p class="text-[9px] text-gray-400 uppercase font-semibold leading-tight">Download for</p>
                                            <p class="text-xs font-bold text-white leading-tight">{{ $appAndroidLabel }}</p>
                                        </div>
                                    </div>
                                    <span class="px-2 py-0.5 rounded-md bg-white/10 text-[9px] font-bold text-pink-400 uppercase tracking-wider">Coming Soon</span>
                                </div>
                            @endif
                        @endif

                        <!-- PWA App Card -->
                        @if($appPwaEnabled)
                            <a href="/manifest.json" 
                               class="flex items-center gap-3 bg-[#181E3B] hover:bg-[#202750] px-3.5 py-2.5 rounded-xl border border-white/10 transition-all group">
                                <div class="w-6 h-6 text-white shrink-0 flex items-center justify-center">
                                    <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                                        <path d="M18.71,19.5C17.88,20.74 17,21.95 15.66,21.97C14.32,22 13.89,21.18 12.37,21.18C10.84,21.18 10.37,21.95 9.1,22C7.79,22.05 6.8,20.68 5.96,19.47C4.25,17 2.94,12.45 4.7,9.39C5.57,7.87 7.13,6.91 8.82,6.88C10.1,6.86 11.32,7.75 12.11,7.75C12.89,7.75 14.37,6.68 15.92,6.84C16.57,6.87 18.39,7.1 19.56,8.82C19.47,8.88 17.39,10.1 17.41,12.63C17.44,15.65 20.06,16.66 20.1,16.67C20.06,16.79 19.67,18.15 18.71,19.5M15.97,4.64C16.56,3.92 16.96,2.92 16.85,1.92C15.97,1.96 14.9,2.51 14.28,3.24C13.73,3.87 13.25,4.89 13.38,5.88C14.37,5.95 15.38,5.36 15.97,4.64Z"/>
                                    </svg>
                                </div>
                                <div class="text-left">
                                    <p class="text-[9px] text-gray-400 uppercase font-semibold leading-tight">WebView Ready</p>
                                    <p class="text-xs font-bold text-white group-hover:text-pink-400 transition-colors leading-tight">{{ $appPwaLabel }}</p>
                                </div>
                            </a>
                        @endif
                    </div>
                </div>
            @endif

        </div>

        <!-- Bottom Copyright & Payment Methods Bar -->
        <div class="py-6 border-t border-[#1E2548] flex flex-col lg:flex-row items-center justify-between text-xs text-gray-400 gap-4 pb-20 md:pb-6">
            
            <!-- Left: Copyright with Tech Googly Link -->
            <div class="text-center lg:text-left">
                <p>&copy; {{ date('Y') }} {{ $restaurantName }} Food Delivery System. {{ $copyrightCustom ?: 'All rights reserved.' }} Developed By <a href="{{ $devCreditUrl }}" target="_blank" rel="noopener noreferrer" class="text-white hover:text-[#F50073] font-semibold underline decoration-pink-500/50 underline-offset-2 transition-colors">{{ $devCreditText }}</a></p>
            </div>

            <!-- Center: Policy Quick Links -->
            @if($policyLinksEnabled)
            <div class="flex items-center gap-3 text-[11px] text-gray-400 flex-wrap justify-center">
                @forelse($policyLinks as $index => $pLink)
                    <a href="{{ $pLink->final_url }}" 
                       @if($pLink->open_in_new_tab) target="_blank" rel="noopener noreferrer" @endif
                       class="hover:text-white transition-colors">
                        {{ $pLink->title }}
                    </a>
                    @if(!$loop->last)
                        <span class="text-gray-600">|</span>
                    @endif
                @empty
                    <a href="{{ route('page.about') }}" class="hover:text-white transition-colors">About Us</a>
                    <span class="text-gray-600">|</span>
                    <a href="{{ route('page.privacy') }}" class="hover:text-white transition-colors">Privacy Policy</a>
                    <span class="text-gray-600">|</span>
                    <a href="{{ route('page.terms') }}" class="hover:text-white transition-colors">Terms & Conditions</a>
                    <span class="text-gray-600">|</span>
                    <a href="{{ route('page.refund') }}" class="hover:text-white transition-colors">Refund Policy</a>
                    <span class="text-gray-600">|</span>
                    <a href="{{ route('page.contact') }}" class="hover:text-white transition-colors">Contact Us</a>
                @endforelse
            </div>
            @endif

            <!-- Right: Payment Methods Badges -->
            @if($paymentMethodsEnabled)
                <div class="flex items-center gap-1.5 bg-white/5 p-1 rounded-lg border border-white/10">
                    <!-- VISA -->
                    <span class="px-1.5 py-0.5 rounded bg-white text-[#1A1F71] font-black text-[10px] tracking-tight">
                        VISA
                    </span>
                    <!-- Mastercard -->
                    <span class="px-1.5 py-0.5 rounded bg-white flex items-center justify-center">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#EB001B] inline-block -mr-1 opacity-90"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-[#F79E1B] inline-block opacity-90"></span>
                    </span>
                    <!-- UPI -->
                    <span class="px-1.5 py-0.5 rounded bg-white text-[#0F7C90] font-black text-[10px] tracking-tighter">
                        UPI
                    </span>
                    <!-- RuPay -->
                    <span class="px-1.5 py-0.5 rounded bg-white text-[#2B388F] font-black text-[10px]">
                        RuPay
                    </span>
                    <!-- COD -->
                    <span class="px-1.5 py-0.5 rounded bg-[#F50073] text-white font-extrabold text-[9px] uppercase tracking-wider">
                        COD
                    </span>
                </div>
            @endif
        </div>
    </div>
</footer>
