/**
 * API V1 Client Examples
 * 
 * Exemples d'utilisation de l'API REST pour applications mobiles
 */

// ============================================================================
// 1. Configuration et Setup
// ============================================================================

const API_BASE_URL = process.env.REACT_APP_API_URL || 'http://localhost:8000/api/v1';

class ApiClient {
    constructor() {
        this.token = this.loadToken();
        this.headers = this.getHeaders();
    }

    loadToken() {
        // Web: localStorage
        if (typeof localStorage !== 'undefined') {
            return localStorage.getItem('auth_token');
        }
        // React Native: AsyncStorage
        // return await AsyncStorage.getItem('auth_token');
        return null;
    }

    getHeaders() {
        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        };

        if (this.token) {
            headers['Authorization'] = `Bearer ${this.token}`;
        }

        return headers;
    }

    setToken(token) {
        this.token = token;
        if (typeof localStorage !== 'undefined') {
            localStorage.setItem('auth_token', token);
        }
        this.headers['Authorization'] = `Bearer ${token}`;
    }

    clearToken() {
        this.token = null;
        if (typeof localStorage !== 'undefined') {
            localStorage.removeItem('auth_token');
        }
        delete this.headers['Authorization'];
    }

    async request(method, endpoint, data = null) {
        const url = `${API_BASE_URL}${endpoint}`;
        const options = {
            method,
            headers: this.getHeaders(),
        };

        if (data) {
            options.body = JSON.stringify(data);
        }

        try {
            const response = await fetch(url, options);
            const json = await response.json();

            // Handle 401 - Token expired
            if (response.status === 401) {
                this.clearToken();
                // Redirect to login
                window.location.href = '/login';
            }

            // Handle 429 - Rate limited
            if (response.status === 429) {
                const retryAfter = response.headers.get('Retry-After');
                throw new Error(`Rate limited. Retry after ${retryAfter}s`);
            }

            if (!response.ok) {
                throw new Error(json.message || 'Request failed');
            }

            return json;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    }

    get(endpoint) {
        return this.request('GET', endpoint);
    }

    post(endpoint, data) {
        return this.request('POST', endpoint, data);
    }

    patch(endpoint, data) {
        return this.request('PATCH', endpoint, data);
    }

    delete(endpoint) {
        return this.request('DELETE', endpoint);
    }
}

const client = new ApiClient();

// ============================================================================
// 2. Authentication Examples
// ============================================================================

async function login(email, password, deviceName) {
    try {
        const response = await client.post('/auth/login', {
            email,
            password,
            device_name: deviceName,
        });

        client.setToken(response.data.token);
        return response.data.user;
    } catch (error) {
        console.error('Login failed:', error.message);
        throw error;
    }
}

async function register(name, email, password, phone = null) {
    try {
        const response = await client.post('/auth/register', {
            name,
            email,
            password,
            password_confirmation: password,
            phone,
        });

        client.setToken(response.data.token);
        return response.data.user;
    } catch (error) {
        console.error('Registration failed:', error.message);
        throw error;
    }
}

async function logout() {
    try {
        await client.post('/auth/logout', {});
        client.clearToken();
    } catch (error) {
        console.error('Logout failed:', error.message);
        client.clearToken(); // Clear anyway
    }
}

async function getCurrentUser() {
    try {
        const response = await client.get('/auth/me');
        return response.data;
    } catch (error) {
        console.error('Failed to get current user:', error.message);
        throw error;
    }
}

// ============================================================================
// 3. Products Examples
// ============================================================================

async function getProducts(options = {}) {
    try {
        const params = new URLSearchParams();
        
        if (options.search) params.append('search', options.search);
        if (options.categoryId) params.append('category_id', options.categoryId);
        if (options.minPrice) params.append('min_price', options.minPrice);
        if (options.maxPrice) params.append('max_price', options.maxPrice);
        if (options.sortBy) params.append('sort_by', options.sortBy);
        if (options.sortOrder) params.append('sort_order', options.sortOrder);
        if (options.perPage) params.append('per_page', options.perPage);
        if (options.cursor) params.append('cursor', options.cursor);

        const endpoint = `/products?${params.toString()}`;
        const response = await client.get(endpoint);
        return response.data;
    } catch (error) {
        console.error('Failed to get products:', error.message);
        throw error;
    }
}

async function getProduct(productId) {
    try {
        const response = await client.get(`/products/${productId}`);
        return response.data;
    } catch (error) {
        console.error(`Failed to get product ${productId}:`, error.message);
        throw error;
    }
}

async function getFeaturedProducts() {
    try {
        const response = await client.get('/products/featured');
        return response.data;
    } catch (error) {
        console.error('Failed to get featured products:', error.message);
        throw error;
    }
}

async function getRelatedProducts(productId) {
    try {
        const response = await client.get(`/products/${productId}/related`);
        return response.data;
    } catch (error) {
        console.error('Failed to get related products:', error.message);
        throw error;
    }
}

// ============================================================================
// 4. Cart Examples
// ============================================================================

async function getCart() {
    try {
        const response = await client.get('/cart');
        return response.data;
    } catch (error) {
        console.error('Failed to get cart:', error.message);
        throw error;
    }
}

async function addToCart(productId, quantity = 1) {
    try {
        const response = await client.post('/cart/add', {
            product_id: productId,
            quantity,
        });
        return response.data;
    } catch (error) {
        console.error('Failed to add to cart:', error.message);
        throw error;
    }
}

async function updateCartItem(productId, quantity) {
    try {
        const response = await client.patch(`/cart/items/${productId}`, {
            quantity,
        });
        return response.data;
    } catch (error) {
        console.error('Failed to update cart item:', error.message);
        throw error;
    }
}

async function removeFromCart(productId) {
    try {
        const response = await client.delete(`/cart/items/${productId}`);
        return response.data;
    } catch (error) {
        console.error('Failed to remove from cart:', error.message);
        throw error;
    }
}

async function clearCart() {
    try {
        const response = await client.delete('/cart');
        return response.data;
    } catch (error) {
        console.error('Failed to clear cart:', error.message);
        throw error;
    }
}

async function applyCoupon(couponCode) {
    try {
        const response = await client.post('/cart/coupon', {
            coupon_code: couponCode,
        });
        return response.data;
    } catch (error) {
        console.error('Failed to apply coupon:', error.message);
        throw error;
    }
}

// ============================================================================
// 5. Orders Examples
// ============================================================================

async function getOrders(options = {}) {
    try {
        const params = new URLSearchParams();
        
        if (options.status) params.append('status', options.status);
        if (options.perPage) params.append('per_page', options.perPage);
        if (options.page) params.append('page', options.page);

        const endpoint = `/orders?${params.toString()}`;
        const response = await client.get(endpoint);
        return response.data;
    } catch (error) {
        console.error('Failed to get orders:', error.message);
        throw error;
    }
}

async function getOrder(orderId) {
    try {
        const response = await client.get(`/orders/${orderId}`);
        return response.data;
    } catch (error) {
        console.error(`Failed to get order ${orderId}:`, error.message);
        throw error;
    }
}

async function createOrder(shippingAddressId, paymentMethod, couponCode = null) {
    try {
        const response = await client.post('/orders', {
            shipping_address_id: shippingAddressId,
            payment_method: paymentMethod,
            coupon_code: couponCode,
        });
        return response.data;
    } catch (error) {
        console.error('Failed to create order:', error.message);
        throw error;
    }
}

async function cancelOrder(orderId) {
    try {
        const response = await client.post(`/orders/${orderId}/cancel`, {});
        return response.data;
    } catch (error) {
        console.error('Failed to cancel order:', error.message);
        throw error;
    }
}

async function getOrderTracking(orderId) {
    try {
        const response = await client.get(`/orders/${orderId}/tracking`);
        return response.data;
    } catch (error) {
        console.error('Failed to get order tracking:', error.message);
        throw error;
    }
}

// ============================================================================
// 6. Account Examples
// ============================================================================

async function getProfile() {
    try {
        const response = await client.get('/account/profile');
        return response.data;
    } catch (error) {
        console.error('Failed to get profile:', error.message);
        throw error;
    }
}

async function updateProfile(updates) {
    try {
        const response = await client.patch('/account/profile', updates);
        return response.data;
    } catch (error) {
        console.error('Failed to update profile:', error.message);
        throw error;
    }
}

async function getAddresses() {
    try {
        const response = await client.get('/account/addresses');
        return response.data;
    } catch (error) {
        console.error('Failed to get addresses:', error.message);
        throw error;
    }
}

async function createAddress(address) {
    try {
        const response = await client.post('/account/addresses', address);
        return response.data;
    } catch (error) {
        console.error('Failed to create address:', error.message);
        throw error;
    }
}

async function updateAddress(addressId, updates) {
    try {
        const response = await client.patch(`/account/addresses/${addressId}`, updates);
        return response.data;
    } catch (error) {
        console.error('Failed to update address:', error.message);
        throw error;
    }
}

async function deleteAddress(addressId) {
    try {
        const response = await client.delete(`/account/addresses/${addressId}`);
        return response.data;
    } catch (error) {
        console.error('Failed to delete address:', error.message);
        throw error;
    }
}

async function getLoyaltyInfo() {
    try {
        const response = await client.get('/account/loyalty');
        return response.data;
    } catch (error) {
        console.error('Failed to get loyalty info:', error.message);
        throw error;
    }
}

async function getLoyaltyHistory(options = {}) {
    try {
        const params = new URLSearchParams();
        
        if (options.perPage) params.append('per_page', options.perPage);
        if (options.page) params.append('page', options.page);

        const endpoint = `/account/loyalty/history?${params.toString()}`;
        const response = await client.get(endpoint);
        return response.data;
    } catch (error) {
        console.error('Failed to get loyalty history:', error.message);
        throw error;
    }
}

// ============================================================================
// 7. Usage Examples
// ============================================================================

/*
// Example: Login and get products
async function exampleFlow() {
    try {
        // Login
        const user = await login('user@example.com', 'password123', 'iPhone 14');
        console.log('Logged in:', user);

        // Get featured products
        const featured = await getFeaturedProducts();
        console.log('Featured products:', featured);

        // Search products
        const products = await getProducts({
            search: 'laptop',
            minPrice: 500,
            maxPrice: 2000,
            sortBy: 'price',
        });
        console.log('Products found:', products);

        // Add to cart
        const cart = await addToCart(1, 2);
        console.log('Added to cart:', cart);

        // Get cart
        const currentCart = await getCart();
        console.log('Current cart:', currentCart);

        // Get profile
        const profile = await getProfile();
        console.log('Profile:', profile);

        // Create order
        const order = await createOrder(1, 'card', 'SUMMER2024');
        console.log('Order created:', order);

        // Get order tracking
        const tracking = await getOrderTracking(order.data.id);
        console.log('Tracking:', tracking);

        // Logout
        await logout();
        console.log('Logged out');
    } catch (error) {
        console.error('Error:', error);
    }
}
*/

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        client,
        login,
        register,
        logout,
        getCurrentUser,
        getProducts,
        getProduct,
        getFeaturedProducts,
        getRelatedProducts,
        getCart,
        addToCart,
        updateCartItem,
        removeFromCart,
        clearCart,
        applyCoupon,
        getOrders,
        getOrder,
        createOrder,
        cancelOrder,
        getOrderTracking,
        getProfile,
        updateProfile,
        getAddresses,
        createAddress,
        updateAddress,
        deleteAddress,
        getLoyaltyInfo,
        getLoyaltyHistory,
    };
}
