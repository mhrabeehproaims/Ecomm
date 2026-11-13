<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('delivery_state', [
                // States
                'andhra_pradesh (AP)',
                'arunachal_pradesh (AR)',
                'assam (AS)',
                'bihar (BR)',
                'chhattisgarh (CG)',
                'goa (GA)',
                'gujarat (GJ)',
                'haryana (HR)',
                'himachal_pradesh (HP)',
                'jharkhand (JH)',
                'karnataka (KA)',
                'kerala (KL)',
                'madhya_pradesh (MP)',
                'maharashtra (MH)',
                'manipur (MN)',
                'meghalaya (ML)',
                'mizoram (MZ)',
                'nagaland (NL)',
                'odisha (OD)',
                'punjab (PB)',
                'rajasthan (RJ)',
                'sikkim (SK)',
                'tamil_nadu (TN)',
                'telangana (TS)',
                'tripura (TR)',
                'uttar_pradesh (UP)',
                'uttarakhand (UK)',
                'west_bengal (WB)',
                // Union Territories
                'andaman_and_nicobar_islands (AN)',
                'chandigarh (CH)',
                'dadra_and_nagar_haveli_and_daman_and_diu (DN)',
                'delhi (DL)',
                'jammu_and_kashmir (JK)',
                'ladakh (LA)',
                'lakshadweep (LD)',
                'puducherry (PY)',
            ])->default('kerala (KL)')->after('delivery_date');
            $table->decimal('delivery_charge')->default(0)->after('delivery_state');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('state');
            $table->dropColumn('delivery_charge');
        });
    }
};
