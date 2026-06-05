import { ref, watch, onMounted } from 'vue';

const cart = ref({
    items: [],
    subtotal: 0,
    grand: 0,
    total: 0,
});

const CART_KEY = 'shop.cart.v1';

const toSafeNumber = (value, fallback = 0) => {
    const n = Number(value);
    return Number.isFinite(n) ? n : fallback;
};

const normalizeItem = (item = {}) => {
    const quantity = Math.max(1, toSafeNumber(item.quantity ?? item.qty, 1));
    const price = toSafeNumber(item.display_price ?? item.price, 0);

    return {
        ...item,
        quantity,
        qty: quantity,
        price,
    };
};

export function useCart() {
    
    const loadCart = () => {
        try {
            const raw = localStorage.getItem(CART_KEY);
            if (raw) {
                const data = JSON.parse(raw);
                if (Array.isArray(data.items)) {
                    cart.value.items = data.items.map(normalizeItem);
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
            const normalized = normalizeItem(item);
            item.quantity = normalized.quantity;
            item.qty = normalized.qty;
            item.price = normalized.price;
            return total + (normalized.price * normalized.quantity);
        }, 0);
        cart.value.grand = cart.value.subtotal;
        cart.value.total = cart.value.subtotal;
    };

    const addToCart = (product, variant = null, qty = 1) => {
        const id = variant ? `${product.id}:${variant.id}` : `${product.id}`;
        const existing = cart.value.items.find(i => i.id === id);

        const qtyToAdd = Math.max(1, toSafeNumber(qty, 1));
        const candidatePrice = variant
            ? (variant.price ?? product.variant_price ?? product.display_price ?? product.price)
            : (product.display_price ?? product.price);
        const safePrice = toSafeNumber(candidatePrice, 0);

        if (existing) {
            const nextQty = Math.max(1, toSafeNumber(existing.quantity ?? existing.qty, 1) + qtyToAdd);
            existing.quantity = nextQty;
            existing.qty = nextQty;
            existing.price = toSafeNumber(existing.price, safePrice);
        } else {
            cart.value.items.push({
                id,
                product_id: product.id,
                product_variant_id: variant ? variant.id : null,
                name: variant ? `${product.name} - ${variant.name}` : product.name,
                price: safePrice,
                display_price: safePrice,
                image: product.image,
                quantity: qtyToAdd,
                qty: qtyToAdd,
                variant_name: variant ? variant.name : null
            });
        }
        calculateTotals();
        saveCart();
    };

    const updateQty = (id, qty) => {
        const item = cart.value.items.find(i => i.id === id);
        if (item) {
            const safeQty = Math.max(1, toSafeNumber(qty, 1));
            item.qty = safeQty;
            item.quantity = safeQty;
            calculateTotals();
            saveCart();
        }
    };

    const updateQuantity = updateQty;

    const removeItem = (id) => {
        cart.value.items = cart.value.items.filter(i => i.id !== id);
        calculateTotals();
        saveCart();
    };

    const removeFromCart = removeItem;

    const clearCart = () => {
        cart.value.items = [];
        calculateTotals();
        saveCart();
    };

    return {
        cart,
        addToCart,
        updateQty,
        updateQuantity,
        removeItem,
        removeFromCart,
        clearCart,
        loadCart
    };
}
