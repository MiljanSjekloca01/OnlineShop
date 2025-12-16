<?php

use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\AuthAdmin;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\WishlistController;

Auth::routes();

Route::get('/', [HomeController::class, 'index'])->name('home.index');
Route::get('/shop',[ShopController::class,'index'])->name('shop.index');
Route::get('/shop/{product_slug}',[ShopController::class,'product_details'])->name('shop.product.details');

//Contact
Route::get('/contact-us',[HomeController::class,'contact_us'])->name('home.contact');
Route::post('/contact/store',[HomeController::class,'contact_store'])->name('home.contact.store');

//Cart
Route::get('/cart',[CartController::class,'index'])->name('cart.index');
Route::post('/cart/add',[CartController::class,'addToCart'])->name('cart.add');
Route::put('/cart/increase-quantity/{rowId}',[CartController::class,'increaseCartQuantity'])->name('cart.qty.increase');
Route::put('/cart/decrease-quantity/{rowId}',[CartController::class,'decreaseCartQuantity'])->name('cart.qty.decrease');
Route::delete('/cart/remove/{rowId}',[CartController::class,'removeItem'])->name('cart.item.remove');
Route::delete('/cart/clear',[CartController::class,'emptyCart'])->name('cart.empty');
//Cart Coupon
Route::post('/cart/apply-coupon',[CartController::class,'apply_coupon_code'])->name('cart.coupon.apply');
Route::delete('/cart/remove-coupon',[CartController::class,'remove_coupon_code'])->name('cart.coupon.remove');

//Wishlist
Route::post('/wishlist/add',[WishlistController::class,'add_to_wishlist'])->name('wishlist.add');
Route::get('/wishlist',[WishlistController::class,'index'])->name('wishlist.index');
Route::delete('/wishlist/item/remove/{rowId}',[WishlistController::class,'remove_item'])->name('wishlist.item.remove');
Route::delete('/wishlist/clear',[WishlistController::class,'empty_wishlist'])->name('wishlist.items.clear');
Route::post('/wishlist/move-to-cart/{rowId}',[WishlistController::class,'move_to_cart'])->name('wishlist.move.to.cart');


Route::middleware(['auth'])->group(function(){
    Route::get('/account-dashboard', [UserController::class, 'index'])->name('user.index');

    //Checkout
    Route::get('/checkout',[CartController::class,'checkout'])->name('cart.checkout');
    Route::post('/place-an-order',[CartController::class,'place_an_order'])->name('cart.place.an.order');
    Route::get('/order-confirmation/{order}',[CartController::class,'order_confirmation'])->name('cart.order.confirmation');
    //Order
    Route::get('/account-orders',[UserController::class,'orders'])->name('user.orders');
    Route::get('/account-orders/{order}/details', [UserController::class, 'order_details'])->name('user.order.details');
    Route::put('/account-orders/{order}/cancel-order',[UserController::class,'cancel_order'])->name('user.order.cancel');
});

Route::middleware(['auth', AuthAdmin::class])->group(function(){
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
    
    Route::get('/admin/brands', [AdminController::class, 'brands'])->name('admin.brands');
    Route::get('/admin/brand/add',[AdminController::class, 'add_brand'])->name('admin.brand.add');
    Route::post('/admin/brand/store',[AdminController::class,'brand_store'])->name('admin.brand.store');
    Route::get('/admin/brand/{id}/edit',[AdminController::class,'brand_edit'])->name('admin.brand.edit');
    Route::put('/admin/brand/update',[AdminController::class,'brand_update'])->name('admin.brand.update');
    Route::delete('/admin/brand/{id}/delete',[AdminController::class,'brand_delete'])->name('admin.brand.delete');

    Route::get('/admin/categories',[AdminController::class,'categories'])->name('admin.categories');
    Route::get('/admin/category/add',[AdminController::class,'category_add'])->name('admin.category.add');
    Route::post('/admin/category/store',[AdminController::class,'category_store'])->name('admin.category.store');
    Route::get('/admin/category/{id}/edit',[AdminController::class,'category_edit'])->name('admin.category.edit');
    Route::put('/admin/category/update',[AdminController::class,'category_update'])->name('admin.category.update');
    Route::delete('/admin/category/{id}/delete',[AdminController::class,'category_delete'])->name('admin.category.delete');

    Route::get('/admin/products',[AdminController::class,'products'])->name('admin.products');
    Route::get('/admin/product/add',[AdminController::class,'add_product'])->name('admin.product.add');
    Route::post('/admin/product/store',[AdminController::class,'product_store'])->name('admin.product.store');
    Route::get('/admin/product/{id}/edit',[AdminController::class,'product_edit'])->name('admin.product.edit');
    Route::put('/admin/product/update',[AdminController::class,'product_update'])->name('admin.product.update');
    Route::delete('/admin/product/{id}/delete',[AdminController::class,'product_delete'])->name('admin.product.delete');

    // Coupons
    Route::get('/admin/coupons',[AdminController::class,'coupons'])->name('admin.coupons');
    Route::get('/admin/coupon/add',[AdminController::class,'coupon_add'])->name('admin.coupon.add');
    Route::post('/admin/coupon/store',[AdminController::class,'coupon_store'])->name('admin.coupon.store');
    Route::get('/admin/coupon/{id}/edit',[AdminController::class,'coupon_edit'])->name('admin.coupon.edit');
    Route::put('/admin/coupon/update',[AdminController::class,'coupon_update'])->name('admin.coupon.update');
    Route::delete('/admin/coupon/{id}/delete',[AdminController::class,'coupon_delete'])->name('admin.coupon.delete');

    // Orders
    Route::get('/admin/orders',[AdminController::class,'orders'])->name('admin.orders');
    Route::get('/admin/order/{order}/details', [AdminController::class, 'order_details'])->name('admin.order.details');
    Route::put('/admin/order/{order}/update-status',[AdminController::class,'update_order_status'])->name('admin.order.status.update');

    //Slider
    Route::get('/admin/slides',[AdminController::class,'slides'])->name('admin.slides');
    Route::get('/admin/slide/add',[AdminController::class,'add_slide'])->name('admin.slide.add');
    Route::post('admin/slide/store',[AdminController::class,'store_slide'])->name('admin.slide.store');
    Route::get('admin/slide/{slide}/edit',[AdminController::class,'edit_slide'])->name('admin.slide.edit');
    Route::put('admin/slide/{slide}/update',[AdminController::class,'update_slide'])->name('admin.slide.update');
    Route::delete('admin/slide/{slide}/delete',[AdminController::class,'delete_slide'])->name('admin.slide.delete');

    //Contacts
    Route::get('/admin/contacts',[AdminController::class,'contacts'])->name('admin.contacts');
    Route::delete('/admin/contacts/{contact}/delete',[AdminController::class,'contact_delete'])->name('admin.contacts.delete');
    Route::put('/admin/contacts/{contact}/read', [AdminController::class,'markRead'])->name('admin.contacts.read');
});