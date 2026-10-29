<?php

namespace Database\Seeders;

class GenerateSampleAssets
{
    public static function generate(): void
    {
        $base = public_path('images');
        @mkdir("$base/defaults", 0755, true);
        @mkdir("$base/categories", 0755, true);
        @mkdir("$base/banners", 0755, true);
        @mkdir("$base/food", 0755, true);

        // 1. Defaults (SVGs)
        file_put_contents("$base/defaults/category-placeholder.svg", self::createCategoryPlaceholderSvg());
        file_put_contents("$base/defaults/food-placeholder.svg", self::createFoodPlaceholderSvg());
        file_put_contents("$base/defaults/banner-placeholder.svg", self::createBannerPlaceholderSvg());

        // 2. Defaults (Real JPEGs & PNGs using GD so browsers never fail MIME decoding)
        self::generateBinaryPlaceholder("$base/defaults/category-placeholder.jpg", 'jpg', 300, 300, 'Category', [245, 0, 112]);
        self::generateBinaryPlaceholder("$base/defaults/category-placeholder.png", 'png', 300, 300, 'Category', [245, 0, 112]);
        self::generateBinaryPlaceholder("$base/defaults/food-placeholder.jpg", 'jpg', 600, 400, 'Food Item', [255, 61, 150]);
        self::generateBinaryPlaceholder("$base/defaults/food-placeholder.png", 'png', 600, 400, 'Food Item', [255, 61, 150]);
        self::generateBinaryPlaceholder("$base/defaults/banner-placeholder.jpg", 'jpg', 1200, 450, 'Special Offer', [245, 0, 112]);
        self::generateBinaryPlaceholder("$base/defaults/banner-placeholder.png", 'png', 1200, 450, 'Special Offer', [245, 0, 112]);

        // 3. Indian Food Categories (Clean, isolated, transparent-background SVGs suitable for category chips)
        file_put_contents("$base/categories/veg-thali.svg", self::createVegThaliSvg());
        file_put_contents("$base/categories/non-veg-thali.svg", self::createNonVegThaliSvg());
        file_put_contents("$base/categories/main-course-veg.svg", self::createPaneerHandiSvg());
        file_put_contents("$base/categories/main-course-non-veg.svg", self::createChickenCurrySvg());
        file_put_contents("$base/categories/rice-roti.svg", self::createRiceRotiSvg());
        file_put_contents("$base/categories/add-ons.svg", self::createAddOnsSvg());
        file_put_contents("$base/categories/beverages.svg", self::createBeveragesSvg());
        file_put_contents("$base/categories/biryani.svg", self::createBiryaniHandiSvg());
        file_put_contents("$base/categories/appetizers.svg", self::createAppetizersSvg());
        file_put_contents("$base/categories/desserts.svg", self::createDessertsSvg());
        file_put_contents("$base/categories/burgers.svg", self::createRollsSvg());

        // 4. Banners
        file_put_contents("$base/banners/banner-biryani.svg", self::createBannerBiryaniSvg());
        file_put_contents("$base/banners/banner-rolls.svg", self::createBannerRollsSvg());

        // Also generate matching PNGs in defaults for legacy hardcoded paths
        self::renderSvgToPngFallback("$base/defaults/category-biryani.png", 320, 240, 'Dum Biryani', [217, 119, 6]);
        self::renderSvgToPngFallback("$base/defaults/category-burgers.png", 320, 240, 'Kathi Rolls', [220, 38, 38]);
        self::renderSvgToPngFallback("$base/banners/banner-biryani.png", 400, 300, 'Dum Biryani', [217, 119, 6]);
        self::renderSvgToPngFallback("$base/banners/banner-rolls.png", 400, 300, 'Kathi Rolls', [220, 38, 38]);
    }

    private static function generateBinaryPlaceholder(string $path, string $format, int $w, int $h, string $text, array $rgb): void
    {
        $im = imagecreatetruecolor($w, $h);
        imagesavealpha($im, true);
        $bgColor = imagecolorallocate($im, $rgb[0], $rgb[1], $rgb[2]);
        imagefill($im, 0, 0, $bgColor);

        // Add subtle border and plate circle
        $white = imagecolorallocate($im, 255, 255, 255);
        $whiteAlpha = imagecolorallocatealpha($im, 255, 255, 255, 90);
        imagefilledellipse($im, (int)($w / 2), (int)($h / 2), (int)($w * 0.7), (int)($h * 0.7), $whiteAlpha);
        imagestring($im, 5, (int)(($w - (strlen($text) * 9)) / 2), (int)(($h / 2) - 8), $text, $white);

        if ($format === 'png') {
            imagepng($im, $path);
        } else {
            imagejpeg($im, $path, 90);
        }
        imagedestroy($im);
    }

    private static function renderSvgToPngFallback(string $path, int $w, int $h, string $text, array $rgb): void
    {
        self::generateBinaryPlaceholder($path, 'png', $w, $h, $text, $rgb);
    }

    // --- High Quality Transparent Category SVGs ---

    private static function createVegThaliSvg(): string
    {
        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200" width="100%" height="100%">
  <defs>
    <radialGradient id="thaliPlatter" cx="50%" cy="50%" r="50%">
      <stop offset="0%" stop-color="#E2E8F0" />
      <stop offset="85%" stop-color="#CBD5E1" />
      <stop offset="100%" stop-color="#94A3B8" />
    </radialGradient>
    <filter id="thaliShadow" x="-10%" y="-10%" width="125%" height="125%">
      <feDropShadow dx="0" dy="5" stdDeviation="6" flood-color="#000000" flood-opacity="0.15" />
    </filter>
  </defs>
  <g filter="url(#thaliShadow)">
    <!-- Stainless Steel Platter -->
    <circle cx="100" cy="100" r="90" fill="url(#thaliPlatter)" stroke="#94A3B8" stroke-width="4" />
    <circle cx="100" cy="100" r="82" fill="#F1F5F9" stroke="#E2E8F0" stroke-width="2" />

    <!-- Rice Mound (Center) -->
    <circle cx="100" cy="100" r="30" fill="#FFFFFF" stroke="#E2E8F0" stroke-width="1.5" />
    <circle cx="100" cy="100" r="28" fill="#F8FAFC" />
    <ellipse cx="100" cy="98" rx="4" ry="2" fill="#16A34A" /> <!-- Mint leaf -->

    <!-- Dal Tadka Bowl (Top) -->
    <circle cx="100" cy="40" r="22" fill="#E2E8F0" stroke="#94A3B8" stroke-width="1.5" />
    <circle cx="100" cy="40" r="19" fill="#F59E0B" />
    <circle cx="100" cy="40" r="17" fill="#D97706" />
    <path d="M 96 38 Q 100 34 104 38 Q 100 42 96 38" fill="#DC2626" opacity="0.8" />

    <!-- Paneer Curry Bowl (Top Right) -->
    <circle cx="145" cy="65" r="21" fill="#E2E8F0" stroke="#94A3B8" stroke-width="1.5" />
    <circle cx="145" cy="65" r="18" fill="#EA580C" />
    <rect x="140" y="60" width="7" height="6" rx="1" fill="#FEF08A" />
    <rect x="146" y="64" width="6" height="5" rx="1" fill="#FEF08A" />

    <!-- Sabzi Bowl (Bottom Right) -->
    <circle cx="145" cy="135" r="21" fill="#E2E8F0" stroke="#94A3B8" stroke-width="1.5" />
    <circle cx="145" cy="135" r="18" fill="#15803D" />
    <circle cx="143" cy="133" r="3" fill="#84CC16" />
    <circle cx="148" cy="137" r="3.5" fill="#DC2626" />

    <!-- Roti / Chapati (Left) -->
    <circle cx="55" cy="95" r="24" fill="#FDE68A" stroke="#D97706" stroke-width="1.5" />
    <circle cx="53" cy="93" r="4" fill="#B45309" opacity="0.3" />
    <circle cx="61" cy="99" r="3" fill="#B45309" opacity="0.3" />
    <circle cx="50" cy="103" r="3.5" fill="#B45309" opacity="0.25" />

    <!-- Salad & Pickle (Bottom Left) -->
    <circle cx="65" cy="145" r="17" fill="#E2E8F0" stroke="#94A3B8" stroke-width="1.5" />
    <circle cx="65" cy="145" r="14" fill="#FFFFFF" />
    <circle cx="62" cy="144" r="5" fill="#22C55E" />
    <circle cx="68" cy="147" r="4" fill="#EF4444" />
  </g>
</svg>
SVG;
    }

    private static function createNonVegThaliSvg(): string
    {
        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200" width="100%" height="100%">
  <defs>
    <radialGradient id="brassPlatter" cx="50%" cy="50%" r="50%">
      <stop offset="0%" stop-color="#FEF08A" />
      <stop offset="85%" stop-color="#EAB308" />
      <stop offset="100%" stop-color="#CA8A04" />
    </radialGradient>
    <filter id="nvThaliShadow" x="-10%" y="-10%" width="125%" height="125%">
      <feDropShadow dx="0" dy="5" stdDeviation="6" flood-color="#000000" flood-opacity="0.18" />
    </filter>
  </defs>
  <g filter="url(#nvThaliShadow)">
    <!-- Brass / Bronze Platter -->
    <circle cx="100" cy="100" r="90" fill="url(#brassPlatter)" stroke="#A16207" stroke-width="4" />
    <circle cx="100" cy="100" r="82" fill="#FEF9C3" stroke="#CA8A04" stroke-width="2" />

    <!-- Fragrant Saffron Rice (Center) -->
    <circle cx="100" cy="100" r="30" fill="#FEF08A" stroke="#FBBF24" stroke-width="1.5" />
    <circle cx="100" cy="98" r="3" fill="#D97706" />
    <ellipse cx="100" cy="100" rx="3" ry="1.5" fill="#15803D" />

    <!-- Chicken Curry / Kosha (Top) -->
    <circle cx="100" cy="40" r="23" fill="#CA8A04" stroke="#854D0E" stroke-width="1.5" />
    <circle cx="100" cy="40" r="20" fill="#991B1B" />
    <!-- Chicken piece -->
    <ellipse cx="98" cy="38" rx="8" ry="6" fill="#78350F" />
    <circle cx="104" cy="42" r="5" fill="#B45309" />

    <!-- Dal Bowl (Top Right) -->
    <circle cx="145" cy="65" r="21" fill="#CA8A04" stroke="#854D0E" stroke-width="1.5" />
    <circle cx="145" cy="65" r="18" fill="#F59E0B" />
    <path d="M 141 63 Q 145 60 149 63" stroke="#DC2626" stroke-width="2" fill="none" />

    <!-- Crispy Fish Fry / Egg Curry (Bottom Right) -->
    <circle cx="145" cy="135" r="21" fill="#CA8A04" stroke="#854D0E" stroke-width="1.5" />
    <circle cx="145" cy="135" r="18" fill="#B45309" />
    <ellipse cx="145" cy="135" rx="9" ry="6" fill="#FBBF24" stroke="#D97706" stroke-width="1" />

    <!-- Flaky Paratha (Left) -->
    <circle cx="55" cy="95" r="25" fill="#FDE68A" stroke="#B45309" stroke-width="1.5" />
    <path d="M 40 85 Q 55 95 70 85" stroke="#B45309" stroke-width="1.5" fill="none" opacity="0.4" />
    <path d="M 42 105 Q 55 95 68 105" stroke="#B45309" stroke-width="1.5" fill="none" opacity="0.4" />

    <!-- Onion Rings & Lemon (Bottom Left) -->
    <circle cx="65" cy="145" r="17" fill="#FFFFFF" stroke="#CA8A04" stroke-width="1.5" />
    <circle cx="62" cy="143" r="5" stroke="#EC4899" stroke-width="2" fill="none" />
    <circle cx="67" cy="147" r="4.5" fill="#FACC15" />
  </g>
</svg>
SVG;
    }

    private static function createPaneerHandiSvg(): string
    {
        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200" width="100%" height="100%">
  <defs>
    <radialGradient id="curryGrad" cx="50%" cy="45%" r="55%">
      <stop offset="0%" stop-color="#FB923C" />
      <stop offset="60%" stop-color="#EA580C" />
      <stop offset="100%" stop-color="#C2410C" />
    </radialGradient>
    <filter id="handiShadow" x="-10%" y="-10%" width="125%" height="125%">
      <feDropShadow dx="0" dy="6" stdDeviation="6" flood-color="#000000" flood-opacity="0.16" />
    </filter>
  </defs>
  <g filter="url(#handiShadow)">
    <!-- Handi Body -->
    <ellipse cx="100" cy="140" rx="65" ry="35" fill="#7C2D12" />
    <ellipse cx="100" cy="115" rx="72" ry="45" fill="#9A3412" />
    <ellipse cx="100" cy="85" rx="68" ry="32" fill="#B45309" />
    <ellipse cx="100" cy="82" rx="64" ry="28" fill="#F59E0B" stroke="#D97706" stroke-width="3" />

    <!-- Rich Tomato Butter Gravy -->
    <ellipse cx="100" cy="82" rx="60" ry="25" fill="url(#curryGrad)" />

    <!-- Cream Swirl -->
    <path d="M 75 80 Q 95 92 120 80 Q 110 73 90 76" fill="none" stroke="#FFFFFF" stroke-width="4" stroke-linecap="round" opacity="0.9" />

    <!-- Soft Paneer Cubes -->
    <rect x="70" y="72" width="16" height="13" rx="2" fill="#FEF08A" stroke="#FDE047" stroke-width="1" />
    <rect x="110" y="75" width="18" height="14" rx="2" fill="#FEF08A" stroke="#FDE047" stroke-width="1" />
    <rect x="92" y="81" width="15" height="12" rx="2" fill="#FEF08A" stroke="#FDE047" stroke-width="1" />

    <!-- Fresh Coriander Garnish -->
    <ellipse cx="98" cy="74" rx="5" ry="3" fill="#16A34A" />
    <ellipse cx="103" cy="72" rx="4" ry="2.5" fill="#15803D" />
    <ellipse cx="94" cy="76" rx="4" ry="2.5" fill="#22C55E" />

    <!-- Brass Handi Rim Handles -->
    <ellipse cx="32" cy="85" rx="6" ry="12" fill="#CA8A04" stroke="#854D0E" stroke-width="1.5" />
    <ellipse cx="168" cy="85" rx="6" ry="12" fill="#CA8A04" stroke="#854D0E" stroke-width="1.5" />
  </g>
</svg>
SVG;
    }

    private static function createChickenCurrySvg(): string
    {
        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200" width="100%" height="100%">
  <defs>
    <radialGradient id="chickenKosha" cx="50%" cy="45%" r="55%">
      <stop offset="0%" stop-color="#DC2626" />
      <stop offset="65%" stop-color="#991B1B" />
      <stop offset="100%" stop-color="#7F1D1D" />
    </radialGradient>
    <filter id="chickenShadow" x="-10%" y="-10%" width="125%" height="125%">
      <feDropShadow dx="0" dy="6" stdDeviation="6" flood-color="#000000" flood-opacity="0.18" />
    </filter>
  </defs>
  <g filter="url(#chickenShadow)">
    <!-- Dark Clay Bowl -->
    <ellipse cx="100" cy="140" rx="68" ry="36" fill="#1C1917" />
    <ellipse cx="100" cy="115" rx="74" ry="45" fill="#292524" />
    <ellipse cx="100" cy="85" rx="70" ry="32" fill="#44403C" stroke="#78716C" stroke-width="3" />

    <!-- Rich Chicken Kosha Gravy -->
    <ellipse cx="100" cy="85" rx="64" ry="26" fill="url(#chickenKosha)" />

    <!-- Tender Spiced Chicken Leg / Drumstick -->
    <g transform="translate(70, 68) rotate(-15)">
      <ellipse cx="25" cy="15" rx="18" ry="12" fill="#78350F" stroke="#92400E" stroke-width="1" />
      <rect x="36" y="11" width="22" height="7" rx="3.5" fill="#F8FAFC" stroke="#E2E8F0" stroke-width="1" />
      <circle cx="58" cy="12" r="4.5" fill="#F8FAFC" />
      <circle cx="58" cy="17" r="4.5" fill="#F8FAFC" />
    </g>

    <!-- Potato / Aloo in Gravy -->
    <ellipse cx="132" cy="90" rx="14" ry="10" fill="#D97706" stroke="#B45309" stroke-width="1" />

    <!-- Green Chili & Ginger Slivers -->
    <path d="M 85 92 Q 95 86 105 92" stroke="#22C55E" stroke-width="3.5" stroke-linecap="round" fill="none" />
    <path d="M 98 80 L 108 83" stroke="#FEF08A" stroke-width="2" stroke-linecap="round" />
    <path d="M 102 77 L 112 81" stroke="#FEF08A" stroke-width="2" stroke-linecap="round" />
  </g>
</svg>
SVG;
    }

    private static function createRiceRotiSvg(): string
    {
        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200" width="100%" height="100%">
  <defs>
    <filter id="rotiShadow" x="-10%" y="-10%" width="125%" height="125%">
      <feDropShadow dx="0" dy="5" stdDeviation="5" flood-color="#000000" flood-opacity="0.15" />
    </filter>
  </defs>
  <g filter="url(#rotiShadow)">
    <!-- Rice Bowl (Right) -->
    <ellipse cx="135" cy="130" rx="45" ry="25" fill="#334155" />
    <ellipse cx="135" cy="115" rx="45" ry="22" fill="#475569" stroke="#64748B" stroke-width="2" />
    <!-- White Fragrant Basmati Rice Mound -->
    <ellipse cx="135" cy="110" rx="40" ry="18" fill="#FFFFFF" />
    <ellipse cx="135" cy="105" rx="34" ry="14" fill="#F8FAFC" />
    <!-- Jeera Cumin garnish -->
    <ellipse cx="130" cy="104" rx="2" ry="0.8" fill="#78350F" />
    <ellipse cx="140" cy="108" rx="2" ry="0.8" fill="#78350F" />
    <ellipse cx="135" cy="102" rx="3" ry="1.5" fill="#16A34A" />

    <!-- Tandoori Roti / Butter Naan (Left, overlapping) -->
    <!-- Roti 1 (Behind) -->
    <ellipse cx="65" cy="115" rx="48" ry="34" fill="#FDE68A" stroke="#D97706" stroke-width="2" transform="rotate(-15, 65, 115)" />
    <!-- Char Marks -->
    <circle cx="55" cy="110" r="3" fill="#92400E" opacity="0.6" />
    <circle cx="70" cy="118" r="4" fill="#92400E" opacity="0.5" />
    <circle cx="80" cy="108" r="3" fill="#92400E" opacity="0.6" />

    <!-- Naan 2 (Front with butter glaze) -->
    <path d="M 40 145 C 30 110 50 75 75 80 C 100 85 95 125 70 145 Z" fill="#FEF08A" stroke="#CA8A04" stroke-width="2" />
    <!-- Butter Glaze & Coriander -->
    <ellipse cx="62" cy="105" rx="8" ry="5" fill="#FACC15" opacity="0.8" />
    <circle cx="65" cy="118" r="2.5" fill="#15803D" />
    <circle cx="58" cy="98" r="2" fill="#15803D" />
    <circle cx="52" cy="120" r="3" fill="#78350F" opacity="0.6" />
    <circle cx="68" cy="92" r="3" fill="#78350F" opacity="0.6" />
  </g>
</svg>
SVG;
    }

    private static function createAddOnsSvg(): string
    {
        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200" width="100%" height="100%">
  <defs>
    <filter id="addonShadow" x="-10%" y="-10%" width="125%" height="125%">
      <feDropShadow dx="0" dy="5" stdDeviation="5" flood-color="#000000" flood-opacity="0.14" />
    </filter>
  </defs>
  <g filter="url(#addonShadow)">
    <!-- Roasted Papad (Left) -->
    <circle cx="65" cy="100" r="45" fill="#FEF08A" stroke="#EAB308" stroke-width="2" />
    <!-- Black pepper / cumin specks on papad -->
    <circle cx="50" cy="85" r="2" fill="#451A03" />
    <circle cx="75" cy="90" r="2.5" fill="#451A03" />
    <circle cx="60" cy="115" r="2" fill="#451A03" />
    <circle cx="80" cy="110" r="1.8" fill="#451A03" />
    <circle cx="45" cy="105" r="2.2" fill="#451A03" />
    <circle cx="68" cy="75" r="1.5" fill="#451A03" />

    <!-- Mint / Cucumber Raita Bowl (Right) -->
    <ellipse cx="135" cy="125" rx="42" ry="24" fill="#3B82F6" opacity="0.15" />
    <circle cx="135" cy="105" r="35" fill="#FFFFFF" stroke="#CBD5E1" stroke-width="2.5" />
    <circle cx="135" cy="105" r="30" fill="#F8FAFC" />
    <!-- Cucumber & spice sprinkle -->
    <circle cx="130" cy="100" r="3" fill="#22C55E" />
    <circle cx="140" cy="108" r="2.5" fill="#22C55E" />
    <circle cx="128" cy="112" r="3" fill="#22C55E" />
    <path d="M 125 102 Q 135 106 145 102" stroke="#EF4444" stroke-width="1.5" fill="none" />
    <!-- Mint sprig -->
    <ellipse cx="135" cy="98" rx="4" ry="2" fill="#15803D" />
  </g>
</svg>
SVG;
    }

    private static function createBeveragesSvg(): string
    {
        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200" width="100%" height="100%">
  <defs>
    <filter id="drinkShadow" x="-10%" y="-10%" width="125%" height="125%">
      <feDropShadow dx="0" dy="5" stdDeviation="5" flood-color="#000000" flood-opacity="0.15" />
    </filter>
  </defs>
  <g filter="url(#drinkShadow)">
    <!-- Terracotta Kulhad / Clay Cup (Left) -->
    <path d="M 45 65 L 55 145 Q 75 152 95 145 L 105 65 Z" fill="#C2410C" stroke="#9A3412" stroke-width="2" />
    <ellipse cx="75" cy="65" rx="30" ry="10" fill="#EA580C" stroke="#9A3412" stroke-width="2" />
    <!-- Frothy Masala Chaas / Lassi -->
    <ellipse cx="75" cy="65" rx="27" ry="8" fill="#FEF9C3" />
    <ellipse cx="75" cy="64" rx="6" ry="2.5" fill="#15803D" /> <!-- Mint -->
    <circle cx="82" cy="65" r="1.5" fill="#B45309" /> <!-- Roasted cumin -->

    <!-- Tall Refreshing Glass (Right) -->
    <path d="M 115 50 L 122 150 Q 142 155 162 150 L 169 50 Z" fill="#38BDF8" fill-opacity="0.35" stroke="#0284C7" stroke-width="2" />
    <!-- Juice Liquid -->
    <path d="M 117 75 L 122 148 Q 142 153 162 148 L 167 75 Z" fill="#F59E0B" fill-opacity="0.85" />
    <!-- Ice cubes -->
    <rect x="130" y="85" width="16" height="16" rx="3" fill="#FFFFFF" fill-opacity="0.6" stroke="#E0F2FE" stroke-width="1" />
    <rect x="136" y="110" width="14" height="14" rx="3" fill="#FFFFFF" fill-opacity="0.5" stroke="#E0F2FE" stroke-width="1" />
    <!-- Straw -->
    <line x1="155" y1="28" x2="135" y2="140" stroke="#EC4899" stroke-width="4.5" stroke-linecap="round" />
    <!-- Lemon Wheel on Rim -->
    <circle cx="115" cy="50" r="16" fill="#FACC15" stroke="#EAB308" stroke-width="2" />
    <circle cx="115" cy="50" r="12" fill="#FEF08A" />
    <line x1="115" y1="38" x2="115" y2="62" stroke="#EAB308" stroke-width="1" />
    <line x1="103" y1="50" x2="127" y2="50" stroke="#EAB308" stroke-width="1" />
  </g>
</svg>
SVG;
    }

    private static function createBiryaniHandiSvg(): string
    {
        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200" width="100%" height="100%">
  <defs>
    <radialGradient id="biryaniRice" cx="50%" cy="40%" r="55%">
      <stop offset="0%" stop-color="#FEF08A" />
      <stop offset="60%" stop-color="#F59E0B" />
      <stop offset="100%" stop-color="#D97706" />
    </radialGradient>
    <filter id="biryaniShadow" x="-10%" y="-10%" width="125%" height="125%">
      <feDropShadow dx="0" dy="6" stdDeviation="6" flood-color="#000000" flood-opacity="0.16" />
    </filter>
  </defs>
  <g filter="url(#biryaniShadow)">
    <!-- Earthen Handi Pot -->
    <ellipse cx="100" cy="145" rx="65" ry="34" fill="#78350F" />
    <ellipse cx="100" cy="118" rx="72" ry="44" fill="#92400E" />
    <ellipse cx="100" cy="85" rx="66" ry="30" fill="#B45309" stroke="#78350F" stroke-width="2.5" />

    <!-- Saffron Layered Basmati Rice -->
    <ellipse cx="100" cy="85" rx="60" ry="24" fill="url(#biryaniRice)" />

    <!-- Succulent Chicken Piece -->
    <ellipse cx="80" cy="80" rx="14" ry="10" fill="#78350F" stroke="#92400E" stroke-width="1" />

    <!-- Golden Boiled Spiced Egg -->
    <ellipse cx="125" cy="84" rx="11" ry="8" fill="#FDE047" stroke="#EAB308" stroke-width="1.5" />

    <!-- Crispy Fried Onions (Birista) -->
    <path d="M 92 76 Q 100 73 108 76" stroke="#451A03" stroke-width="2.5" stroke-linecap="round" fill="none" />
    <path d="M 88 88 Q 96 92 104 88" stroke="#451A03" stroke-width="2.5" stroke-linecap="round" fill="none" />
    <path d="M 108 83 Q 116 80 120 85" stroke="#451A03" stroke-width="2" stroke-linecap="round" fill="none" />

    <!-- Fresh Mint & Roasted Cashews -->
    <ellipse cx="98" cy="82" rx="4" ry="2" fill="#15803D" />
    <ellipse cx="103" cy="80" rx="3.5" ry="2" fill="#16A34A" />
    <ellipse cx="112" cy="77" rx="5" ry="3" fill="#FEF3C7" stroke="#FDE68A" stroke-width="1" />
  </g>
</svg>
SVG;
    }

    private static function createAppetizersSvg(): string
    {
        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200" width="100%" height="100%">
  <defs>
    <filter id="appShadow" x="-10%" y="-10%" width="125%" height="125%">
      <feDropShadow dx="0" dy="5" stdDeviation="5" flood-color="#000000" flood-opacity="0.15" />
    </filter>
  </defs>
  <g filter="url(#appShadow)">
    <!-- Serving Platter -->
    <ellipse cx="100" cy="120" rx="80" ry="40" fill="#F8FAFC" stroke="#E2E8F0" stroke-width="3" />

    <!-- Golden Crispy Samosa 1 -->
    <polygon points="65,70 35,120 95,120" fill="#F59E0B" stroke="#D97706" stroke-width="2" />
    <polygon points="65,70 55,120 95,120" fill="#D97706" opacity="0.3" />

    <!-- Golden Crispy Samosa 2 -->
    <polygon points="120,65 85,115 145,115" fill="#FBBF24" stroke="#D97706" stroke-width="2" />
    <polygon points="120,65 110,115 145,115" fill="#D97706" opacity="0.3" />

    <!-- Paneer Tikka / Kebab Skewer -->
    <rect x="135" y="90" width="18" height="14" rx="2" fill="#EA580C" stroke="#C2410C" stroke-width="1.5" />
    <circle cx="144" cy="97" r="2.5" fill="#451A03" />

    <!-- Mint Chutney Dip Bowl (Center) -->
    <circle cx="100" cy="125" r="18" fill="#FFFFFF" stroke="#CBD5E1" stroke-width="2" />
    <circle cx="100" cy="125" r="14" fill="#15803D" />
  </g>
</svg>
SVG;
    }

    private static function createDessertsSvg(): string
    {
        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200" width="100%" height="100%">
  <defs>
    <filter id="sweetShadow" x="-10%" y="-10%" width="125%" height="125%">
      <feDropShadow dx="0" dy="5" stdDeviation="5" flood-color="#000000" flood-opacity="0.15" />
    </filter>
  </defs>
  <g filter="url(#sweetShadow)">
    <!-- Earthen Sweet Bowl -->
    <ellipse cx="100" cy="125" rx="65" ry="32" fill="#78350F" />
    <ellipse cx="100" cy="105" rx="62" ry="24" fill="#92400E" stroke="#B45309" stroke-width="2" />

    <!-- Sweet Rose-Cardamom Syrup -->
    <ellipse cx="100" cy="105" rx="55" ry="18" fill="#F59E0B" opacity="0.75" />

    <!-- Golden Brown Gulab Jamun 1 -->
    <circle cx="80" cy="100" r="18" fill="#78350F" stroke="#92400E" stroke-width="1.5" />
    <ellipse cx="76" cy="94" rx="5" ry="3" fill="#D97706" opacity="0.6" />
    <!-- Pistachio sliver -->
    <ellipse cx="78" cy="98" rx="2.5" ry="1" fill="#22C55E" />

    <!-- Gulab Jamun 2 -->
    <circle cx="118" cy="102" r="17" fill="#78350F" stroke="#92400E" stroke-width="1.5" />
    <ellipse cx="114" cy="96" rx="5" ry="3" fill="#D97706" opacity="0.6" />
    <ellipse cx="116" cy="100" rx="2.5" ry="1" fill="#22C55E" />

    <!-- Gulab Jamun 3 (Front Center) -->
    <circle cx="100" cy="112" r="17" fill="#6B2807" stroke="#92400E" stroke-width="1.5" />
    <ellipse cx="96" cy="106" rx="5" ry="3" fill="#D97706" opacity="0.6" />
    <ellipse cx="99" cy="110" rx="3" ry="1.2" fill="#22C55E" />
  </g>
</svg>
SVG;
    }

    private static function createRollsSvg(): string
    {
        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200" width="100%" height="100%">
  <defs>
    <filter id="rollShadow" x="-10%" y="-10%" width="125%" height="125%">
      <feDropShadow dx="0" dy="5" stdDeviation="5" flood-color="#000000" flood-opacity="0.15" />
    </filter>
  </defs>
  <g filter="url(#rollShadow)">
    <!-- Kolkata Kathi Roll 1 (Diagonal) -->
    <g transform="translate(45, 45) rotate(25)">
      <!-- Silver Foil Wrap (Bottom) -->
      <rect x="20" y="60" width="36" height="55" rx="4" fill="#CBD5E1" stroke="#94A3B8" stroke-width="1.5" />
      <path d="M 20 75 Q 38 78 56 75" stroke="#94A3B8" stroke-width="1" fill="none" />
      
      <!-- Paratha Roll (Top) -->
      <rect x="22" y="20" width="32" height="45" rx="16" fill="#FDE68A" stroke="#D97706" stroke-width="2" />
      <!-- Brown Toast Marks -->
      <circle cx="32" cy="35" r="3" fill="#92400E" opacity="0.5" />
      <circle cx="42" cy="45" r="3.5" fill="#92400E" opacity="0.5" />

      <!-- Spiced Chicken & Onion Filling Peeking Out -->
      <circle cx="34" cy="18" r="5" fill="#991B1B" />
      <circle cx="42" cy="16" r="4.5" fill="#78350F" />
      <ellipse cx="38" cy="22" rx="4" ry="2" fill="#22C55E" />
      <path d="M 32 14 Q 38 20 44 14" stroke="#EC4899" stroke-width="2" fill="none" />
    </g>

    <!-- Kolkata Kathi Roll 2 (Behind) -->
    <g transform="translate(95, 35) rotate(35)">
      <rect x="18" y="60" width="34" height="50" rx="4" fill="#E2E8F0" stroke="#94A3B8" stroke-width="1.5" />
      <rect x="20" y="20" width="30" height="45" rx="15" fill="#FEF08A" stroke="#D97706" stroke-width="2" />
      <circle cx="30" cy="18" r="4.5" fill="#991B1B" />
    </g>
  </g>
</svg>
SVG;
    }

    // --- Placeholders ---

    private static function createCategoryPlaceholderSvg(): string
    {
        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200" width="100%" height="100%">
  <rect width="200" height="200" rx="28" fill="#FFF3F8" />
  <circle cx="100" cy="90" r="50" fill="#FF006B" fill-opacity="0.12" />
  <text x="100" y="102" font-size="44" text-anchor="middle" dominant-baseline="middle">🍽️</text>
  <text x="100" y="160" font-family="-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif" font-size="14" font-weight="700" fill="#FF006B" text-anchor="middle">Our Menu</text>
</svg>
SVG;
    }

    private static function createFoodPlaceholderSvg(): string
    {
        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 400" width="100%" height="100%">
  <rect width="600" height="400" fill="#FFF3F8" />
  <circle cx="300" cy="180" r="100" fill="#FF006B" fill-opacity="0.1" />
  <text x="300" y="200" font-size="70" text-anchor="middle" dominant-baseline="middle">🍲</text>
  <text x="300" y="320" font-family="-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif" font-size="22" font-weight="700" fill="#1F1F39" text-anchor="middle">My 2nd Wife Specialty</text>
  <text x="300" y="350" font-family="-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif" font-size="14" fill="#6B7280" text-anchor="middle">Freshly Cooked Homestyle Delights</text>
</svg>
SVG;
    }

    private static function createBannerPlaceholderSvg(): string
    {
        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 350" width="100%" height="100%">
  <rect width="800" height="350" rx="24" fill="#E8F8F2" stroke="#D5EFE5" stroke-width="2" />
  <text x="60" y="100" font-family="-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif" font-size="16" font-weight="700" fill="#FF006B">Uplifting anytime</text>
  <text x="60" y="150" font-family="-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif" font-size="26" font-weight="800" fill="#1F1F39">Flat 20% off on Dum Biryani</text>
  <text x="60" y="185" font-family="-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif" font-size="14" fill="#4B5563">Slow dum-cooked with royal saffron & spices</text>
  <rect x="60" y="225" width="140" height="42" rx="21" fill="#1F1F39" />
  <text x="130" y="251" font-family="-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif" font-size="14" font-weight="700" fill="#FFFFFF" text-anchor="middle">Order Now</text>
  <circle cx="640" cy="175" r="100" fill="#FFFFFF" fill-opacity="0.8" />
  <text x="640" y="195" font-size="90" text-anchor="middle" dominant-baseline="middle">🍗</text>
</svg>
SVG;
    }

    // --- Homepage Banners SVGs ---

    private static function createBannerBiryaniSvg(): string
    {
        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 300" width="100%" height="100%">
  <defs>
    <radialGradient id="bannerPlatter" cx="50%" cy="50%" r="50%">
      <stop offset="0%" stop-color="#FFFFFF" />
      <stop offset="80%" stop-color="#FEF3C7" />
      <stop offset="100%" stop-color="#FDE68A" />
    </radialGradient>
    <filter id="bShadow" x="-10%" y="-10%" width="125%" height="125%">
      <feDropShadow dx="0" dy="8" stdDeviation="10" flood-color="#000000" flood-opacity="0.18" />
    </filter>
  </defs>
  <g filter="url(#bShadow)">
    <!-- Serving Platter -->
    <ellipse cx="200" cy="180" rx="170" ry="85" fill="url(#bannerPlatter)" stroke="#F59E0B" stroke-width="3" />

    <!-- Earthen Handi Pot -->
    <ellipse cx="200" cy="175" rx="100" ry="45" fill="#78350F" />
    <ellipse cx="200" cy="140" rx="110" ry="60" fill="#92400E" />
    <ellipse cx="200" cy="100" rx="100" ry="42" fill="#B45309" stroke="#78350F" stroke-width="3" />

    <!-- Saffron Biryani Rice Layer -->
    <ellipse cx="200" cy="100" rx="92" ry="34" fill="#F59E0B" />
    <ellipse cx="200" cy="98" rx="84" ry="28" fill="#FBBF24" />

    <!-- Chicken Drumstick -->
    <g transform="translate(140, 70) rotate(-10)">
      <ellipse cx="40" cy="25" rx="30" ry="18" fill="#78350F" stroke="#92400E" stroke-width="2" />
      <rect x="60" y="20" width="30" height="10" rx="5" fill="#F8FAFC" stroke="#E2E8F0" stroke-width="1.5" />
      <circle cx="90" cy="22" r="6" fill="#F8FAFC" />
      <circle cx="90" cy="28" r="6" fill="#F8FAFC" />
    </g>

    <!-- Golden Spiced Egg -->
    <ellipse cx="240" cy="105" rx="18" ry="13" fill="#FDE047" stroke="#EAB308" stroke-width="2" />

    <!-- Birista, Mint & Cashews -->
    <path d="M 180 90 Q 200 85 220 90" stroke="#451A03" stroke-width="3" stroke-linecap="round" fill="none" />
    <ellipse cx="195" cy="102" rx="7" ry="3.5" fill="#15803D" />
    <ellipse cx="208" cy="98" rx="7" ry="4" fill="#FEF3C7" stroke="#FDE68A" stroke-width="1" />
  </g>
</svg>
SVG;
    }

    private static function createBannerRollsSvg(): string
    {
        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 300" width="100%" height="100%">
  <defs>
    <filter id="rShadow" x="-10%" y="-10%" width="125%" height="125%">
      <feDropShadow dx="0" dy="8" stdDeviation="10" flood-color="#000000" flood-opacity="0.18" />
    </filter>
  </defs>
  <g filter="url(#rShadow)">
    <!-- Wooden Board Base -->
    <rect x="30" y="160" width="340" height="90" rx="18" fill="#78350F" stroke="#92400E" stroke-width="3" />
    <rect x="35" y="165" width="330" height="75" rx="14" fill="#92400E" />

    <!-- Kolkata Kathi Roll 1 (Golden flaky paratha) -->
    <g transform="translate(70, 75) rotate(20)">
      <!-- Silver Foil -->
      <rect x="25" y="80" width="48" height="75" rx="6" fill="#CBD5E1" stroke="#94A3B8" stroke-width="2" />
      <!-- Paratha -->
      <rect x="28" y="25" width="42" height="60" rx="20" fill="#FDE68A" stroke="#D97706" stroke-width="2.5" />
      <circle cx="42" cy="45" r="4" fill="#92400E" opacity="0.6" />
      <circle cx="55" cy="58" r="4.5" fill="#92400E" opacity="0.6" />
      <!-- Filling -->
      <circle cx="44" cy="22" r="7" fill="#991B1B" />
      <circle cx="56" cy="20" r="6" fill="#78350F" />
      <path d="M 40 18 Q 48 25 58 18" stroke="#EC4899" stroke-width="2.5" fill="none" />
      <ellipse cx="50" cy="26" rx="5" ry="2.5" fill="#22C55E" />
    </g>

    <!-- Kolkata Kathi Roll 2 -->
    <g transform="translate(145, 65) rotate(30)">
      <rect x="25" y="80" width="46" height="70" rx="6" fill="#E2E8F0" stroke="#94A3B8" stroke-width="2" />
      <rect x="28" y="25" width="40" height="60" rx="18" fill="#FEF08A" stroke="#D97706" stroke-width="2.5" />
      <circle cx="44" cy="22" r="6" fill="#991B1B" />
      <ellipse cx="50" cy="25" rx="4" ry="2" fill="#22C55E" />
    </g>

    <!-- Refreshing Iced Cola / Beverage Glass on the Right -->
    <g transform="translate(260, 60)">
      <path d="M 20 40 L 30 160 Q 55 166 80 160 L 90 40 Z" fill="#1C1917" stroke="#44403C" stroke-width="2.5" />
      <!-- Liquid & Bubbles -->
      <path d="M 22 65 L 30 158 Q 55 164 80 158 L 88 65 Z" fill="#7C2D12" opacity="0.9" />
      <circle cx="50" cy="80" r="3" fill="#FFFFFF" opacity="0.6" />
      <circle cx="65" cy="95" r="2.5" fill="#FFFFFF" opacity="0.6" />
      <circle cx="42" cy="115" r="2" fill="#FFFFFF" opacity="0.5" />
      <circle cx="58" cy="130" r="2.5" fill="#FFFFFF" opacity="0.5" />
      <!-- Straw -->
      <line x1="75" y1="10" x2="48" y2="150" stroke="#EF4444" stroke-width="5" stroke-linecap="round" />
      <!-- Lemon slice -->
      <circle cx="25" cy="40" r="14" fill="#FACC15" stroke="#CA8A04" stroke-width="2" />
    </g>
  </g>
</svg>
SVG;
    }
}
