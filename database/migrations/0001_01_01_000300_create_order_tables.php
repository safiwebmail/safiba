<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('measurement_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name')->default('My Measurements');
            $table->decimal('height', 8, 2)->nullable();
            $table->decimal('chest', 8, 2)->nullable();
            $table->decimal('waist', 8, 2)->nullable();
            $table->decimal('hip', 8, 2)->nullable();
            $table->decimal('shoulder', 8, 2)->nullable();
            $table->decimal('sleeve', 8, 2)->nullable();
            $table->decimal('neck', 8, 2)->nullable();
            $table->decimal('shirt_length', 8, 2)->nullable();
            $table->decimal('trouser_length', 8, 2)->nullable();
            $table->decimal('wrist', 8, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('shop_id')->constrained('shops');
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->text('customer_address')->nullable();
            $table->string('type')->default('product')->index();
            $table->string('status')->default('pending')->index();
            $table->string('payment_status')->default('unpaid')->index();
            $table->string('payment_method')->default('cod')->index();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('delivery_fee', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->string('delivery_method')->default('pickup')->index();
            $table->text('notes')->nullable();
            $table->foreignId('tailor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('expected_completion_date')->nullable();
            $table->dateTime('assigned_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->dateTime('cancelled_at')->nullable();
            $table->string('cancelled_reason')->nullable();
            $table->timestamps();
            $table->index(['shop_id', 'status']);
            $table->index(['user_id', 'created_at']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->integer('quantity')->default(1);
            $table->decimal('price', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->string('size')->nullable();
            $table->string('color')->nullable();
            $table->string('garment_type')->nullable();
            $table->string('fabric')->nullable();
            $table->foreignId('measurement_profile_id')->nullable()->constrained()->nullOnDelete();
            $table->json('measurements')->nullable();
            $table->string('design_image')->nullable();
            $table->text('instructions')->nullable();
            $table->timestamps();
        });

        Schema::create('order_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('status');
            $table->string('note')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shop_id')->constrained('shops');
            $table->decimal('amount', 12, 2);
            $table->string('method')->default('cod');
            $table->string('status')->default('completed');
            $table->string('reference')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->foreignId('added_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shop_id')->constrained('shops');
            $table->string('method')->default('pickup');
            $table->text('address')->nullable();
            $table->decimal('fee', 12, 2)->default(0);
            $table->string('status')->default('pending')->index();
            $table->foreignId('staff_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('delivery_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deliveries');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('order_status_histories');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('measurement_profiles');
    }
};