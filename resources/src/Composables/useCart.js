import { ref, watch, onMounted } from 'vue';

const cart = ref({
    items: [],
    subtotal: 0,
    grand: 0
});

const CART_KEY = 'shop.cart.v1';

export function useCart() {
    
    const loadCart = () => {
        try {
            const raw = localStorage.getItem(CART_KEY);
            if (raw) {
                const data = JSON.parse(raw);
                if (Array.isArray(data.items)) {
                    cart.value.items = data.items;
                    calculateTotals();
                }
            }
        } catch (e) {
            console.error('Failed to load cart', e);
        }
    };

    const saveCart = () => {
        localStorage.setItem(CART_KEY, JSON.stringify({
            items: cart.value.items,
            updated_at: new Date().toISOString()
        }));
    };

    const calculateTotals = () => {
        cart.value.subtotal = cart.value.items.reduce((total, item) => {
            return total + (parseFloat(item.price) * parseFloat(item.qty));
        }, 0);
        cart.value.grand = cart.value.subtotal;
    };

    const addToCart = (product, variant = null, qty = 1) => {
        const id = variant ? `${product.id}:${variant.id}` : `${product.id}`;
        const existing = cart.value.items.find(i => i.id === id);

        if (existing) {
            existing.qty += qty;
        } else {
            cart.value.items.push({
                id,
                product_id: product.id,
                product_variant_id: variant ? variant.id : null,
                name: variant ? `${product.name} - ${variant.name}` : product.name,
                price: variant ? (variant.price || product.price) : product.price,
                image: product.image,
                qty: qty,
                variant_name: variant ? variant.name : null
            });
        }
        calculateTotals();
        saveCart();
    };

    const updateQty = (id, qty) => {
        const item = cart.value.items.find(i => i.id === id);
        if (item) {
            item.qty = Math.max(1, qty);
            calculateTotals();
            saveCart();
        }
    };

    const removeItem = (id) => {
        cart.value.items = cart.value.items.filter(i => i.id !== id);
        calculateTotals();
        saveCart();
    };

    const clearCart = () => {
        cart.value.items = [];
        calculateTotals();
        saveCart();
    };

    return {
        cart,
        addToCart,
        updateQty,
        removeItem,
        clearCart,
        loadCart
    };
}
