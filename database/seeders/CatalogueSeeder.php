<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Season;
use App\Models\SizeGroup;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class CatalogueSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Categories
        $mens = Category::firstOrCreate(['slug' => 'mens-wear'], ['name' => "Men's Wear", 'code' => 'MEN', 'hsn_code' => '6203', 'default_tax_rate' => 5.00]);
        $womens = Category::firstOrCreate(['slug' => 'womens-wear'], ['name' => "Women's Wear", 'code' => 'WOM', 'hsn_code' => '6204', 'default_tax_rate' => 5.00]);
        $kids = Category::firstOrCreate(['slug' => 'kids-wear'], ['name' => "Kids' Wear", 'code' => 'KID', 'hsn_code' => '6209', 'default_tax_rate' => 5.00]);
        $ethnic = Category::firstOrCreate(['slug' => 'ethnic-wear'], ['name' => 'Ethnic Wear', 'code' => 'ETH', 'hsn_code' => '5208', 'default_tax_rate' => 5.00]);
        $acc = Category::firstOrCreate(['slug' => 'accessories'], ['name' => 'Accessories', 'code' => 'ACC', 'hsn_code' => '4202', 'default_tax_rate' => 12.00]);

        // Subcategories
        $mensShirts = Category::firstOrCreate(['slug' => 'mens-shirts'], ['name' => "Men's Shirts", 'parent_id' => $mens->id, 'code' => 'MSH', 'hsn_code' => '6203', 'default_tax_rate' => 5.00]);
        $mensTrousers = Category::firstOrCreate(['slug' => 'mens-trousers'], ['name' => "Men's Trousers", 'parent_id' => $mens->id, 'code' => 'MTR', 'hsn_code' => '6203', 'default_tax_rate' => 5.00]);
        $womensKurtis = Category::firstOrCreate(['slug' => 'womens-kurtis'], ['name' => 'Kurtis & Tunics', 'parent_id' => $womens->id, 'code' => 'WKT', 'hsn_code' => '6204', 'default_tax_rate' => 5.00]);
        $womensSarees = Category::firstOrCreate(['slug' => 'womens-sarees'], ['name' => 'Silk & Cotton Sarees', 'parent_id' => $ethnic->id, 'code' => 'WSR', 'hsn_code' => '5208', 'default_tax_rate' => 5.00]);

        // 2. Brands
        $bNextDNA = Brand::firstOrCreate(['code' => 'NDNA'], ['name' => 'NextDNA Originals']);
        $bRaymond = Brand::firstOrCreate(['code' => 'RAYM'], ['name' => 'Raymond Classic']);
        $bAllenSolly = Brand::firstOrCreate(['code' => 'ASOL'], ['name' => 'Allen Solly']);
        $bManyavar = Brand::firstOrCreate(['code' => 'MANV'], ['name' => 'Manyavar']);
        $bFabIndia = Brand::firstOrCreate(['code' => 'FABI'], ['name' => 'FabIndia Ethnos']);

        // 3. Seasons
        $sSummer = Season::firstOrCreate(['code' => 'SS26'], ['name' => 'Summer Spring 2026']);
        $sFestive = Season::firstOrCreate(['code' => 'FEST26'], ['name' => 'Diwali Festive 2026']);
        $sWinter = Season::firstOrCreate(['code' => 'AW26'], ['name' => 'Autumn Winter 2026']);

        // 4. Size Groups
        SizeGroup::firstOrCreate(['code' => 'ALPHA'], [
            'name' => 'Standard Alpha (XS - XXL)',
            'sizes' => ['XS', 'S', 'M', 'L', 'XL', 'XXL'],
        ]);
        SizeGroup::firstOrCreate(['code' => 'WAIST'], [
            'name' => 'Waist Numeric (28 - 38)',
            'sizes' => ['28', '30', '32', '34', '36', '38'],
        ]);
        SizeGroup::firstOrCreate(['code' => 'KIDS'], [
            'name' => 'Kids Ages (2Y - 10Y)',
            'sizes' => ['2Y', '4Y', '6Y', '8Y', '10Y'],
        ]);

        // 5. Suppliers
        $supTirupur = Supplier::firstOrCreate(['gstin' => '33AABCT1234F1Z1'], [
            'name' => 'Tirupur Cotton Mills Pvt Ltd',
            'company_name' => 'Tirupur Mills Group',
            'phone' => '+91 421 2233445',
            'email' => 'sales@tirupurcotton.com',
            'address' => '54 Cotton Market Road',
            'city' => 'Tirupur',
            'state' => 'Tamil Nadu',
            'state_code' => '33',
            'opening_balance' => 0.00,
        ]);

        $supSurat = Supplier::firstOrCreate(['gstin' => '24AABCS5678G1Z2'], [
            'name' => 'Surat Silk & Sarees Hub',
            'company_name' => 'Surat Textile Traders',
            'phone' => '+91 261 2445566',
            'email' => 'orders@suratsilk.com',
            'address' => '102 Ring Road Textile Market',
            'city' => 'Surat',
            'state' => 'Gujarat',
            'state_code' => '24',
            'opening_balance' => 0.00,
        ]);

        // 6. Products and Variants
        // Product 1: Men's Oxford Shirt
        $pShirt = Product::firstOrCreate(['article_code' => 'ART-MSH-001'], [
            'category_id' => $mensShirts->id,
            'brand_id' => $bNextDNA->id,
            'season_id' => $sSummer->id,
            'name' => 'NextDNA Royal Oxford Cotton Shirt',
            'hsn_code' => '6203',
            'tax_rate' => 5.00,
            'is_tax_inclusive' => true,
            'description' => 'Premium 100% combed cotton formal oxford weave shirt with pearl buttons.',
            'gender' => 'Men',
            'fit' => 'Slim Fit',
            'fabric' => '100% Cotton',
            'pattern' => 'Solid',
            'min_selling_price' => 799.00,
            'is_bundle' => false,
            'is_active' => true,
        ]);

        $shirtVariants = [
            ['size' => 'S', 'color' => 'Navy Blue', 'sku' => 'NDNA-MSH-NV-S', 'barcode' => '890123400101', 'cost' => 450, 'sp' => 899, 'mrp' => 1299],
            ['size' => 'M', 'color' => 'Navy Blue', 'sku' => 'NDNA-MSH-NV-M', 'barcode' => '890123400102', 'cost' => 450, 'sp' => 899, 'mrp' => 1299],
            ['size' => 'L', 'color' => 'Navy Blue', 'sku' => 'NDNA-MSH-NV-L', 'barcode' => '890123400103', 'cost' => 450, 'sp' => 899, 'mrp' => 1299],
            ['size' => 'XL', 'color' => 'Navy Blue', 'sku' => 'NDNA-MSH-NV-XL', 'barcode' => '890123400104', 'cost' => 450, 'sp' => 899, 'mrp' => 1299],
            ['size' => 'M', 'color' => 'Crisp White', 'sku' => 'NDNA-MSH-WH-M', 'barcode' => '890123400105', 'cost' => 450, 'sp' => 899, 'mrp' => 1299],
            ['size' => 'L', 'color' => 'Crisp White', 'sku' => 'NDNA-MSH-WH-L', 'barcode' => '890123400106', 'cost' => 450, 'sp' => 899, 'mrp' => 1299],
        ];

        foreach ($shirtVariants as $v) {
            ProductVariant::firstOrCreate(['barcode' => $v['barcode']], [
                'product_id' => $pShirt->id,
                'size' => $v['size'],
                'color' => $v['color'],
                'sku' => $v['sku'],
                'purchase_cost' => $v['cost'],
                'selling_price' => $v['sp'],
                'mrp' => $v['mrp'],
                'wholesale_price' => $v['sp'] * 0.85,
                'low_stock_threshold' => 5,
                'reorder_quantity' => 15,
                'is_active' => true,
            ]);
        }

        // Product 2: Men's Stretch Chinos
        $pChino = Product::firstOrCreate(['article_code' => 'ART-MTR-002'], [
            'category_id' => $mensTrousers->id,
            'brand_id' => $bRaymond->id,
            'season_id' => $sSummer->id,
            'name' => 'Raymond Urban Comfort Stretch Chinos',
            'hsn_code' => '6203',
            'tax_rate' => 5.00,
            'is_tax_inclusive' => true,
            'description' => 'Four-way stretch twill chino pants with wrinkle-resistant finish.',
            'gender' => 'Men',
            'fit' => 'Regular Fit',
            'fabric' => '98% Cotton 2% Spandex',
            'pattern' => 'Solid',
            'min_selling_price' => 999.00,
            'is_bundle' => false,
            'is_active' => true,
        ]);

        $chinoVariants = [
            ['size' => '30', 'color' => 'Khaki', 'sku' => 'RAY-CHN-KH-30', 'barcode' => '890123400201', 'cost' => 600, 'sp' => 1199, 'mrp' => 1699],
            ['size' => '32', 'color' => 'Khaki', 'sku' => 'RAY-CHN-KH-32', 'barcode' => '890123400202', 'cost' => 600, 'sp' => 1199, 'mrp' => 1699],
            ['size' => '34', 'color' => 'Khaki', 'sku' => 'RAY-CHN-KH-34', 'barcode' => '890123400203', 'cost' => 600, 'sp' => 1199, 'mrp' => 1699],
            ['size' => '32', 'color' => 'Charcoal Grey', 'sku' => 'RAY-CHN-CG-32', 'barcode' => '890123400204', 'cost' => 600, 'sp' => 1199, 'mrp' => 1699],
            ['size' => '34', 'color' => 'Charcoal Grey', 'sku' => 'RAY-CHN-CG-34', 'barcode' => '890123400205', 'cost' => 600, 'sp' => 1199, 'mrp' => 1699],
        ];

        foreach ($chinoVariants as $v) {
            ProductVariant::firstOrCreate(['barcode' => $v['barcode']], [
                'product_id' => $pChino->id,
                'size' => $v['size'],
                'color' => $v['color'],
                'sku' => $v['sku'],
                'purchase_cost' => $v['cost'],
                'selling_price' => $v['sp'],
                'mrp' => $v['mrp'],
                'wholesale_price' => $v['sp'] * 0.85,
                'low_stock_threshold' => 4,
                'reorder_quantity' => 10,
                'is_active' => true,
            ]);
        }

        // Product 3: Women's Anarkali Kurti
        $pKurti = Product::firstOrCreate(['article_code' => 'ART-WKT-003'], [
            'category_id' => $womensKurtis->id,
            'brand_id' => $bFabIndia->id,
            'season_id' => $sFestive->id,
            'name' => 'FabIndia Floral Gold Embroidered Kurti',
            'hsn_code' => '6204',
            'tax_rate' => 5.00,
            'is_tax_inclusive' => true,
            'description' => 'Chanderi silk festive anarkali kurti with intricate zardozi collar embroidery.',
            'gender' => 'Women',
            'fit' => 'Anarkali Flare',
            'fabric' => 'Chanderi Silk',
            'pattern' => 'Floral Embroidered',
            'min_selling_price' => 899.00,
            'is_bundle' => false,
            'is_active' => true,
        ]);

        $kurtiVariants = [
            ['size' => 'M', 'color' => 'Maroon Gold', 'sku' => 'FAB-KRT-MG-M', 'barcode' => '890123400301', 'cost' => 550, 'sp' => 1099, 'mrp' => 1599],
            ['size' => 'L', 'color' => 'Maroon Gold', 'sku' => 'FAB-KRT-MG-L', 'barcode' => '890123400302', 'cost' => 550, 'sp' => 1099, 'mrp' => 1599],
            ['size' => 'XL', 'color' => 'Maroon Gold', 'sku' => 'FAB-KRT-MG-XL', 'barcode' => '890123400303', 'cost' => 550, 'sp' => 1099, 'mrp' => 1599],
            ['size' => 'M', 'color' => 'Teal Blue', 'sku' => 'FAB-KRT-TB-M', 'barcode' => '890123400304', 'cost' => 550, 'sp' => 1099, 'mrp' => 1599],
            ['size' => 'L', 'color' => 'Teal Blue', 'sku' => 'FAB-KRT-TB-L', 'barcode' => '890123400305', 'cost' => 550, 'sp' => 1099, 'mrp' => 1599],
        ];

        foreach ($kurtiVariants as $v) {
            ProductVariant::firstOrCreate(['barcode' => $v['barcode']], [
                'product_id' => $pKurti->id,
                'size' => $v['size'],
                'color' => $v['color'],
                'sku' => $v['sku'],
                'purchase_cost' => $v['cost'],
                'selling_price' => $v['sp'],
                'mrp' => $v['mrp'],
                'wholesale_price' => $v['sp'] * 0.85,
                'low_stock_threshold' => 5,
                'reorder_quantity' => 12,
                'is_active' => true,
            ]);
        }

        // Product 4: Kanchipuram Pure Silk Saree
        $pSaree = Product::firstOrCreate(['article_code' => 'ART-WSR-004'], [
            'category_id' => $womensSarees->id,
            'brand_id' => $bManyavar->id,
            'season_id' => $sFestive->id,
            'name' => 'Kanchipuram Pure Silk Bridal Saree',
            'hsn_code' => '5208',
            'tax_rate' => 5.00,
            'is_tax_inclusive' => true,
            'description' => 'Authentic handwoven Kanchipuram silk saree with pure zari temple border and rich pallu.',
            'gender' => 'Women',
            'fit' => 'Traditional 6 Yards',
            'fabric' => 'Pure Mulberry Silk',
            'pattern' => 'Zari Woven Brocade',
            'min_selling_price' => 3999.00,
            'is_bundle' => false,
            'is_active' => true,
        ]);

        $sareeVariants = [
            ['size' => 'Free Size', 'color' => 'Ruby Red', 'sku' => 'MAN-SAR-RR-FS', 'barcode' => '890123400401', 'cost' => 2500, 'sp' => 4499, 'mrp' => 5999],
            ['size' => 'Free Size', 'color' => 'Emerald Green', 'sku' => 'MAN-SAR-EG-FS', 'barcode' => '890123400402', 'cost' => 2500, 'sp' => 4499, 'mrp' => 5999],
            ['size' => 'Free Size', 'color' => 'Royal Purple', 'sku' => 'MAN-SAR-RP-FS', 'barcode' => '890123400403', 'cost' => 2500, 'sp' => 4499, 'mrp' => 5999],
        ];

        foreach ($sareeVariants as $v) {
            ProductVariant::firstOrCreate(['barcode' => $v['barcode']], [
                'product_id' => $pSaree->id,
                'size' => $v['size'],
                'color' => $v['color'],
                'sku' => $v['sku'],
                'purchase_cost' => $v['cost'],
                'selling_price' => $v['sp'],
                'mrp' => $v['mrp'],
                'wholesale_price' => $v['sp'] * 0.85,
                'low_stock_threshold' => 2,
                'reorder_quantity' => 5,
                'is_active' => true,
            ]);
        }
    }
}
