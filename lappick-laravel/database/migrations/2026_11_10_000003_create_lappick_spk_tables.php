<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for LapPick SPK Core Tables:
     * 1. criteria (9 decision criteria C1..C9)
     * 2. laptops (949 cleaned laptops preserving original IDs 1..949)
     * 3. preset_profiles (4 AHP-validated persona profiles)
     * 4. decision_histories (saved AHP + TOPSIS runs)
     */
    public function up(): void
    {
        Schema::create('criteria', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('name_id', 100);
            $table->string('name_en', 100);
            $table->string('type', 16)->index(); // 'benefit' | 'cost'
            $table->string('unit', 24);
            $table->string('column_name', 64);
            $table->double('default_weight')->default(0.111111);
            $table->unsignedTinyInteger('sort_order')->default(1);
            $table->timestamps();
        });

        Schema::create('laptops', function (Blueprint $table) {
            // Preserve exact CSV IDs (1..949) for 1e-6 tie-breaker contract (score DESC, id ASC)
            $table->unsignedBigInteger('id')->primary();
            $table->text('name');
            $table->string('brand', 64)->index();
            $table->string('type_name', 64)->index();
            $table->string('os', 64)->default('Windows');
            $table->string('processor_brand', 64)->default('Intel');
            $table->string('cpu_name', 160);
            $table->string('gpu_name', 160);
            $table->string('ram_type', 32)->default('ddr4');
            $table->integer('ram_type_score')->default(4);
            $table->boolean('has_ssd')->default(true)->index();
            $table->boolean('expandable_memory')->default(false);
            $table->boolean('touchscreen')->default(false);
            $table->double('dedicated_vram_gb')->default(0.0);
            $table->double('screen_inches')->default(15.6);
            $table->string('screen_resolution', 64)->default('1080');
            $table->text('source_link')->nullable();

            // 9 Decision Criteria Columns (C1..C9)
            $table->unsignedBigInteger('price_inr')->default(0);
            $table->unsignedBigInteger('price_idr')->index(); // C1 (Cost)
            $table->integer('ram_gb')->index();               // C2 (Benefit)
            $table->integer('storage_gb')->default(512);
            $table->double('storage_effective_gb');           // C3 (Benefit)
            $table->integer('cpu_ranking')->index();          // C4 (Cost)
            $table->double('gpu_benchmark');                  // C5 (Benefit)
            $table->double('battery_hours');                  // C6 (Benefit)
            $table->double('weight_kg');                      // C7 (Cost - exact float for 1e-6 contract)
            $table->double('user_rating');                    // C8 (Benefit)
            $table->integer('refresh_rate_hz')->index();      // C9 (Benefit)

            $table->boolean('is_imputed')->default(false);
            $table->timestamps();

            $table->index(['brand', 'price_idr', 'ram_gb'], 'idx_laptops_brand_price_ram');
        });

        Schema::create('preset_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 40)->unique();
            $table->string('name_id', 100);
            $table->string('name_en', 100);
            $table->text('description_id');
            $table->text('description_en');
            $table->json('pairwise_matrix');
            $table->json('weights');
            $table->double('lambda_max');
            $table->double('ci');
            $table->double('ri')->default(1.45);
            $table->double('cr');
            $table->boolean('is_consistent')->default(true);
            $table->timestamps();
        });

        Schema::create('decision_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('profile_slug', 40)->nullable()->index();
            $table->string('mode', 20)->default('preset')->index(); // 'preset' | 'custom'
            $table->json('pairwise_matrix')->nullable();
            $table->json('weights');
            $table->double('lambda_max')->default(9.0);
            $table->double('ci')->default(0.0);
            $table->double('cr')->default(0.0);
            $table->boolean('is_consistent')->default(true);
            $table->json('filters')->nullable();
            $table->json('top_results');
            $table->unsignedInteger('total_candidates')->default(949);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('decision_histories');
        Schema::dropIfExists('preset_profiles');
        Schema::dropIfExists('laptops');
        Schema::dropIfExists('criteria');
    }
};

