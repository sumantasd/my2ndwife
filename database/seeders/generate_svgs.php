<?php

$categories = [
    [
        'file' => 'public/images/categories/veg-thali.svg',
        'title' => 'Veg Thali',
        'subtitle' => 'Traditional Pure Veg',
        'icon' => '🍱',
        'color1' => '#166534',
        'color2' => '#22C55E',
        'badge' => 'PURE VEG'
    ],
    [
        'file' => 'public/images/categories/non-veg-thali.svg',
        'title' => 'Non-Veg Thali',
        'subtitle' => 'Curry, Rice & Accompaniments',
        'icon' => '🍛',
        'color1' => '#991B1B',
        'color2' => '#EF4444',
        'badge' => 'POPULAR'
    ],
    [
        'file' => 'public/images/categories/main-course-veg.svg',
        'title' => 'Main Course (Veg)',
        'subtitle' => 'Curries, Paneer & Dal',
        'icon' => '🥘',
        'color1' => '#065F46',
        'color2' => '#10B981',
        'badge' => 'HOMESTYLE'
    ],
    [
        'file' => 'public/images/categories/main-course-non-veg.svg',
        'title' => 'Main Course (Non-Veg)',
        'subtitle' => 'Chicken, Fish & Egg Special',
        'icon' => '🍗',
        'color1' => '#831843',
        'color2' => '#EC4899',
        'badge' => 'CHEF SPECIAL'
    ],
    [
        'file' => 'public/images/categories/rice-roti.svg',
        'title' => 'Rice & Roti',
        'subtitle' => 'Steamed Rice & Fresh Breads',
        'icon' => '🫓',
        'color1' => '#92400E',
        'color2' => '#F59E0B',
        'badge' => 'FRESH BAKED'
    ],
    [
        'file' => 'public/images/categories/add-ons.svg',
        'title' => 'Add-Ons',
        'subtitle' => 'Salad, Papad & Dal',
        'icon' => '🥗',
        'color1' => '#374151',
        'color2' => '#6B7280',
        'badge' => 'SIDES'
    ],
    [
        'file' => 'public/images/categories/beverages.svg',
        'title' => 'Beverages',
        'subtitle' => 'Water & Chilled Buttermilk',
        'icon' => '🥛',
        'color1' => '#0E7490',
        'color2' => '#06B6D4',
        'badge' => 'REFRESHING'
    ],
];

foreach ($categories as $cat) {
    $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 320" width="100%" height="100%">
  <defs>
    <linearGradient id="cgrad" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="{$cat['color1']}" />
      <stop offset="100%" stop-color="{$cat['color2']}" />
    </linearGradient>
    <radialGradient id="cglow" cx="50%" cy="35%" r="60%">
      <stop offset="0%" stop-color="#ffffff" stop-opacity="0.3" />
      <stop offset="100%" stop-color="#000000" stop-opacity="0.3" />
    </radialGradient>
  </defs>
  <rect width="320" height="320" rx="32" fill="url(#cgrad)" />
  <rect width="320" height="320" rx="32" fill="url(#cglow)" />
  <circle cx="160" cy="130" r="75" fill="#ffffff" fill-opacity="0.18" />
  <circle cx="160" cy="130" r="55" fill="#ffffff" fill-opacity="0.22" stroke="#ffffff" stroke-width="2" stroke-opacity="0.4" stroke-dasharray="6,4" />
  <text x="160" y="148" font-size="70" text-anchor="middle" dominant-baseline="middle">{$cat['icon']}</text>
  
  <rect x="30" y="22" width="90" height="22" rx="11" fill="#000000" fill-opacity="0.35" />
  <text x="75" y="37" font-family="-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif" font-size="10" font-weight="800" fill="#ffffff" text-anchor="middle" letter-spacing="0.5">{$cat['badge']}</text>

  <rect x="0" y="235" width="320" height="85" fill="#18181B" fill-opacity="0.88" />
  <text x="160" y="270" font-family="-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif" font-size="18" font-weight="700" fill="#ffffff" text-anchor="middle">{$cat['title']}</text>
  <text x="160" y="295" font-family="-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif" font-size="12" fill="#E4E4E7" text-anchor="middle">{$cat['subtitle']}</text>
</svg>
SVG;
    file_put_contents($cat['file'], $svg);
}

$foods = [
    // Category 1: Veg Thali
    [
        'file' => 'public/images/food/regular-veg-thali.svg',
        'name' => 'Regular Veg Thali',
        'price' => '₹80',
        'desc' => 'Dal, Sabzi, Rice, 4 Roti, Salad',
        'icon' => '🍱',
        'color1' => '#166534',
        'color2' => '#22C55E',
        'badge' => 'VEG THALI'
    ],
    [
        'file' => 'public/images/food/special-veg-thali.svg',
        'name' => 'Special Veg Thali',
        'price' => '₹120',
        'desc' => '2 Sabzi, Dal, Rice, 4 Roti, Salad, Papad',
        'icon' => '🍛',
        'color1' => '#14532D',
        'color2' => '#16A34A',
        'badge' => 'SPECIAL THALI'
    ],

    // Category 2: Non-Veg Thali
    [
        'file' => 'public/images/food/egg-thali.svg',
        'name' => 'Egg Thali',
        'price' => '₹100',
        'desc' => 'Egg Curry, Dal, Rice, 4 Roti',
        'icon' => '🥚',
        'color1' => '#9A3412',
        'color2' => '#EA580C',
        'badge' => 'NON-VEG'
    ],
    [
        'file' => 'public/images/food/chicken-thali.svg',
        'name' => 'Chicken Thali',
        'price' => '₹160',
        'desc' => 'Chicken Curry, Rice, 4 Roti, Salad',
        'icon' => '🍗',
        'color1' => '#831843',
        'color2' => '#E11D48',
        'badge' => 'BESTSELLER'
    ],
    [
        'file' => 'public/images/food/fish-thali.svg',
        'name' => 'Fish Thali',
        'price' => '₹180',
        'desc' => 'Fish Curry, Rice, 4 Roti, Salad',
        'icon' => '🐟',
        'color1' => '#1E3A8A',
        'color2' => '#3B82F6',
        'badge' => 'BENGALI SPECIAL'
    ],

    // Category 3: Main Course (Veg)
    [
        'file' => 'public/images/food/paneer-butter-masala.svg',
        'name' => 'Paneer Butter Masala',
        'price' => '₹140',
        'desc' => 'Rich velvety cashew & tomato gravy with cottage cheese',
        'icon' => '🧀',
        'color1' => '#B45309',
        'color2' => '#F59E0B',
        'badge' => 'CHEF PICK'
    ],
    [
        'file' => 'public/images/food/mixed-veg.svg',
        'name' => 'Mixed Veg',
        'price' => '₹100',
        'desc' => 'Fresh seasonal farm vegetables gently spiced',
        'icon' => '🥕',
        'color1' => '#065F46',
        'color2' => '#10B981',
        'badge' => 'FRESH VEG'
    ],
    [
        'file' => 'public/images/food/aloo-posto.svg',
        'name' => 'Aloo Posto',
        'price' => '₹90',
        'desc' => 'Authentic Bengali style potato curry in poppy seed paste',
        'icon' => '🥔',
        'color1' => '#78350F',
        'color2' => '#D97706',
        'badge' => 'AUTHENTIC BENGALI'
    ],
    [
        'file' => 'public/images/food/dal-tadka.svg',
        'name' => 'Dal Tadka',
        'price' => '₹80',
        'desc' => 'Yellow lentils tempered with ghee, cumin, garlic and chili',
        'icon' => '🥣',
        'color1' => '#A16207',
        'color2' => '#EAB308',
        'badge' => 'GHEE TADKA'
    ],

    // Category 4: Main Course (Non-Veg)
    [
        'file' => 'public/images/food/chicken-curry.svg',
        'name' => 'Chicken Curry',
        'price' => '₹140',
        'desc' => 'Tender chicken simmered in aromatic homestyle gravy',
        'icon' => '🍗',
        'color1' => '#991B1B',
        'color2' => '#DC2626',
        'badge' => 'HOMESTYLE'
    ],
    [
        'file' => 'public/images/food/chicken-kosha.svg',
        'name' => 'Chicken Kosha',
        'price' => '₹180',
        'desc' => 'Slow-cooked dry spicy Bengali chicken roast in rich masala',
        'icon' => '🌶️',
        'color1' => '#7F1D1D',
        'color2' => '#B91C1C',
        'badge' => 'KOSHA SPECIAL'
    ],
    [
        'file' => 'public/images/food/egg-curry.svg',
        'name' => 'Egg Curry (2 pcs)',
        'price' => '₹90',
        'desc' => 'Fried boiled eggs in a spiced onion-tomato gravy',
        'icon' => '🥚',
        'color1' => '#9A3412',
        'color2' => '#F97316',
        'badge' => '2 PIECES'
    ],
    [
        'file' => 'public/images/food/fish-curry.svg',
        'name' => 'Fish Curry',
        'price' => '₹160',
        'desc' => 'Fresh catch cooked in traditional mustard and mustard-oil gravy',
        'icon' => '🐟',
        'color1' => '#1E40AF',
        'color2' => '#2563EB',
        'badge' => 'FRESH CATCH'
    ],

    // Category 5: Rice & Roti
    [
        'file' => 'public/images/food/plain-rice.svg',
        'name' => 'Plain Rice',
        'price' => '₹40',
        'desc' => 'Steamed long-grain fragrant white rice',
        'icon' => '🍚',
        'color1' => '#475569',
        'color2' => '#94A3B8',
        'badge' => 'STEAMED'
    ],
    [
        'file' => 'public/images/food/jeera-rice.svg',
        'name' => 'Jeera Rice',
        'price' => '₹60',
        'desc' => 'Basmati rice tossed with toasted cumin seeds and pure ghee',
        'icon' => '🌾',
        'color1' => '#713F12',
        'color2' => '#CA8A04',
        'badge' => 'PURE GHEE'
    ],
    [
        'file' => 'public/images/food/roti.svg',
        'name' => 'Roti (1 pc)',
        'price' => '₹8',
        'desc' => 'Hot whole-wheat phulka straight from the tawa',
        'icon' => '🫓',
        'color1' => '#78350F',
        'color2' => '#B45309',
        'badge' => 'TAWA PHULKA'
    ],
    [
        'file' => 'public/images/food/paratha.svg',
        'name' => 'Paratha (1 pc)',
        'price' => '₹15',
        'desc' => 'Crispy layered triangular golden paratha',
        'icon' => '🥞',
        'color1' => '#854D0E',
        'color2' => '#EAB308',
        'badge' => 'CRISPY LAYERED'
    ],

    // Category 6: Add-Ons
    [
        'file' => 'public/images/food/salad.svg',
        'name' => 'Salad',
        'price' => '₹20',
        'desc' => 'Crisp sliced cucumber, onion, tomato and green chili with lemon',
        'icon' => '🥗',
        'color1' => '#065F46',
        'color2' => '#34D399',
        'badge' => 'FRESH CUT'
    ],
    [
        'file' => 'public/images/food/papad.svg',
        'name' => 'Papad',
        'price' => '₹10',
        'desc' => 'Crunchy roasted or fried urad dal papad',
        'icon' => '🍘',
        'color1' => '#78350F',
        'color2' => '#F59E0B',
        'badge' => 'CRUNCHY'
    ],
    [
        'file' => 'public/images/food/extra-dal.svg',
        'name' => 'Extra Dal',
        'price' => '₹30',
        'desc' => 'Additional bowl of homestyle tempered dal',
        'icon' => '🥣',
        'color1' => '#854D0E',
        'color2' => '#FBBF24',
        'badge' => 'EXTRA PORTION'
    ],

    // Category 7: Beverages
    [
        'file' => 'public/images/food/mineral-water.svg',
        'name' => 'Mineral Water',
        'price' => '₹20',
        'desc' => 'Packaged sealed packaged drinking mineral water (1 Litre)',
        'icon' => '💧',
        'color1' => '#0369A1',
        'color2' => '#38BDF8',
        'badge' => 'CHILLED 1L'
    ],
    [
        'file' => 'public/images/food/buttermilk.svg',
        'name' => 'Buttermilk',
        'price' => '₹25',
        'desc' => 'Refreshing spiced masala chaas churned with roasted cumin and mint',
        'icon' => '🥛',
        'color1' => '#0F766E',
        'color2' => '#2DD4BF',
        'badge' => 'MASALA CHAAS'
    ],
];

foreach ($foods as $food) {
    $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 400" width="100%" height="100%">
  <defs>
    <linearGradient id="fgrad" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="{$food['color1']}" />
      <stop offset="100%" stop-color="{$food['color2']}" />
    </linearGradient>
    <radialGradient id="fglow" cx="50%" cy="40%" r="60%">
      <stop offset="0%" stop-color="#ffffff" stop-opacity="0.25" />
      <stop offset="100%" stop-color="#000000" stop-opacity="0.35" />
    </radialGradient>
  </defs>
  <rect width="600" height="400" fill="url(#fgrad)" />
  <rect width="600" height="400" fill="url(#fglow)" />
  
  <!-- Outer Plate / Thali Ring -->
  <circle cx="300" cy="165" r="115" fill="#ffffff" fill-opacity="0.12" stroke="#ffffff" stroke-width="3" stroke-opacity="0.3" />
  <circle cx="300" cy="165" r="95" fill="#ffffff" fill-opacity="0.18" />
  <circle cx="300" cy="165" r="80" fill="#000000" fill-opacity="0.15" />
  
  <!-- Food Icon -->
  <text x="300" y="190" font-size="82" text-anchor="middle" dominant-baseline="middle">{$food['icon']}</text>
  
  <!-- Price Pill -->
  <g transform="translate(480, 24)">
    <rect x="0" y="0" width="95" height="34" rx="17" fill="#F50070" filter="drop-shadow(0 2px 4px rgba(0,0,0,0.3))" />
    <text x="47" y="22" font-family="-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif" font-size="16" font-weight="800" fill="#ffffff" text-anchor="middle">{$food['price']}</text>
  </g>
  
  <!-- Badge Pill -->
  <g transform="translate(25, 24)">
    <rect x="0" y="0" width="130" height="28" rx="14" fill="#000000" fill-opacity="0.45" />
    <text x="65" y="19" font-family="-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif" font-size="11" font-weight="800" fill="#ffffff" text-anchor="middle" letter-spacing="0.5">{$food['badge']}</text>
  </g>

  <!-- Bottom Details Bar -->
  <rect x="0" y="285" width="600" height="115" fill="#18181B" fill-opacity="0.92" />
  <text x="300" y="328" font-family="-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif" font-size="22" font-weight="800" fill="#ffffff" text-anchor="middle">{$food['name']}</text>
  <text x="300" y="360" font-family="-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif" font-size="13" fill="#D4D4D8" text-anchor="middle">{$food['desc']}</text>
  <text x="300" y="385" font-family="-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif" font-size="11" font-weight="600" fill="#F50070" text-anchor="middle">MY 2ND WIFE • FRESHLY PREPARED</text>
</svg>
SVG;
    file_put_contents($food['file'], $svg);
}

echo "Generated " . count($categories) . " categories and " . count($foods) . " food SVG assets.\n";
