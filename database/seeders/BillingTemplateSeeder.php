<?php

namespace Database\Seeders;

use App\Models\BillingTemplate;
use Illuminate\Database\Seeder;

class BillingTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'id' => 1,
                'template_name' => 'Modern Corporate (Clean Blue)',
                'template_json' => json_encode([
                    'key' => 'modern_blue',
                    'badge' => 'Corporate & Clean',
                    'primary_color' => '#0068e1',
                    'accent_color' => '#eff6ff',
                    'layout' => 'standard',
                    'description' => 'A crisp, professional corporate template with bold blue accents, structured summary boxes, and clean typography.',
                    'show_logo' => true,
                    'show_bank' => true,
                    'footer_note' => 'Thank you for shopping with us! For questions regarding this invoice, contact customer care.',
                    'terms' => '1. Payment is due within 15 days of invoice date. 2. Return requests accepted within 7 days with original invoice.',
                ]),
                'preview_image' => 'assets/images/template_modern.png',
                'status' => 1,
            ],
            [
                'id' => 2,
                'template_name' => 'GST Tax Professional (Official Compliance)',
                'template_json' => json_encode([
                    'key' => 'gst_tax',
                    'badge' => 'Indian GST Compliant',
                    'primary_color' => '#0f172a',
                    'accent_color' => '#f8fafc',
                    'layout' => 'gst_tax',
                    'description' => 'Comprehensive Indian GST format with Supplier & Buyer GSTIN, HSN/SAC codes, state code, CGST/SGST/IGST tax rates, and authorized signatory.',
                    'show_logo' => true,
                    'show_bank' => true,
                    'footer_note' => 'This is a computer generated tax invoice and complies with GST Rule 46.',
                    'terms' => '1. Goods once sold will not be taken back without original packaging. 2. Subject to local jurisdiction only.',
                ]),
                'preview_image' => 'assets/images/template_gst.png',
                'status' => 1,
            ],
            [
                'id' => 3,
                'template_name' => 'Elegant Luxury (Monochrome Boutique)',
                'template_json' => json_encode([
                    'key' => 'elegant_dark',
                    'badge' => 'Luxury & Minimalist',
                    'primary_color' => '#18181b',
                    'accent_color' => '#fafafa',
                    'layout' => 'elegant',
                    'description' => 'Sophisticated, high-end monochrome aesthetic with refined serif headers, minimalist borders, and contemporary spacing.',
                    'show_logo' => true,
                    'show_bank' => false,
                    'footer_note' => 'We truly appreciate your custom. Crafted with care and passion.',
                    'terms' => 'Exchange or store credit available within 14 days of purchase.',
                ]),
                'preview_image' => 'assets/images/template_elegant.png',
                'status' => 1,
            ],
            [
                'id' => 4,
                'template_name' => 'Thermal POS Slip (80mm Receipt)',
                'template_json' => json_encode([
                    'key' => 'thermal_pos',
                    'badge' => 'Compact 80mm POS',
                    'primary_color' => '#000000',
                    'accent_color' => '#ffffff',
                    'layout' => 'thermal',
                    'description' => 'High-contrast, compact monospaced slip optimized for 80mm thermal receipt roll printers and quick counter billing.',
                    'show_logo' => false,
                    'show_bank' => false,
                    'footer_note' => '*** HAVE A GREAT DAY ***',
                    'terms' => 'Keep receipt for exchanges within 7 days.',
                ]),
                'preview_image' => 'assets/images/template_thermal.png',
                'status' => 1,
            ],
        ];

        foreach ($templates as $t) {
            BillingTemplate::updateOrCreate(
                ['id' => $t['id']],
                [
                    'user_id' => 1,
                    'template_name' => $t['template_name'],
                    'template_json' => $t['template_json'],
                    'preview_image' => $t['preview_image'],
                    'status' => $t['status'],
                ]
            );
        }
    }
}
