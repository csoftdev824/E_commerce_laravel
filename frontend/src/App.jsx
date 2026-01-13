import { useState, useEffect } from 'react';
import { authAPI, productAPI, cartAPI, orderAPI } from './services/api';
import Login from './components/Login';
import Register from './components/Register';
import ProductList from './components/ProductList';
import Cart from './components/Cart';

function App() {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);
  const [showRegister, setShowRegister] = useState(false);
  const [cartItems, setCartItems] = useState([]);
  const [showCart, setShowCart] = useState(false);

  useEffect(() => {
    checkAuth();
  }, []);

  const checkAuth = async () => {
    const token = localStorage.getItem('token');
    if (token) {
      try {
        const response = await authAPI.getUser();
        setUser(response.data);
        loadCart();
      } catch (error) {
        localStorage.removeItem('token');
      }
    }
    setLoading(false);
  };

  const loadCart = async () => {
    try {
      const response = await cartAPI.getAll();
      setCartItems(response.data);
    } catch (error) {
      console.error('Failed to load cart:', error);
    }
  };

  const handleLogin = async (email, password) => {
    try {
      const response = await authAPI.login({ email, password });
      localStorage.setItem('token', response.data.token || response.data);
      await checkAuth();
    } catch (error) {
      alert(error.response?.data?.message || 'Login failed');
      throw error;
    }
  };

  const handleRegister = async (name, email, password, passwordConfirmation) => {
    try {
      await authAPI.register({ name, email, password, password_confirmation : passwordConfirmation });
      await handleLogin(email, password);
    } catch (error) {
      alert(error.response?.data?.message || 'Registration failed');
      throw error;
    }
  };

  const handleLogout = async () => {
    try {
      await authAPI.logout();
    } catch (error) {
      console.error('Logout error:', error);
    }
    localStorage.removeItem('token');
    setUser(null);
    setCartItems([]);
  };

  const handleAddToCart = async (productId, quantity = 1) => {
    try {
      await cartAPI.add({ product_id: productId, quantity });
      await loadCart();
    } catch (error) {
      alert(error.response?.data?.message || 'Failed to add to cart');
    }
  };

  const handleUpdateCart = async (cartItemId, quantity) => {
    try {
      await cartAPI.update(cartItemId, { quantity });
      await loadCart();
    } catch (error) {
      alert(error.response?.data?.message || 'Failed to update cart');
    }
  };

  const handleRemoveFromCart = async (cartItemId) => {
    try {
      await cartAPI.remove(cartItemId);
      await loadCart();
    } catch (error) {
      alert(error.response?.data?.message || 'Failed to remove from cart');
    }
  };

  const handleCheckout = async () => {
    if (cartItems.length === 0) {
      alert('Your cart is empty!');
      return;
    }

    if (!confirm('Are you sure you want to place this order?')) {
      return;
    }

    try {
      const response = await orderAPI.checkout();
      alert('Order placed successfully!');
      await loadCart(); // Cart should be empty now
      setShowCart(false); // Close cart view
    } catch (error) {
      const errorMessage = error.response?.data?.message || 
                          error.response?.data?.errors?.cart?.[0] ||
                          'Failed to place order';
      alert(errorMessage);
    }
  };

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="text-xl">Loading...</div>
      </div>
    );
  }

  if (!user) {
    return (
      <div className="min-h-screen bg-gray-100 flex items-center justify-center">
        <div className="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
          {showRegister ? (
            <Register
              onRegister={handleRegister}
              onSwitchToLogin={() => setShowRegister(false)}
            />
          ) : (
            <Login
              onLogin={handleLogin}
              onSwitchToRegister={() => setShowRegister(true)}
            />
          )}
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-50">
      <nav className="bg-white shadow-md">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex justify-between items-center h-16">
            <h1 className="text-2xl font-bold text-gray-800">E-Commerce Store</h1>
            <div className="flex items-center gap-4">
              <span className="text-gray-700">Welcome, {user.name}</span>
              <button
                onClick={() => setShowCart(!showCart)}
                className="relative bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
              >
                Cart ({cartItems.length})
              </button>
              <button
                onClick={handleLogout}
                className="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600"
              >
                Logout
              </button>
            </div>
          </div>
        </div>
      </nav>

      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {showCart ? (
          <Cart
            cartItems={cartItems}
            onUpdate={handleUpdateCart}
            onRemove={handleRemoveFromCart}
            onCheckout={handleCheckout}
            onClose={() => setShowCart(false)}
          />
        ) : (
          <ProductList onAddToCart={handleAddToCart} />
        )}
      </div>
    </div>
  );
}

export default App;
