function Cart({ cartItems, onUpdate, onRemove, onCheckout, onClose }) {
  const calculateTotal = () => {
    return cartItems.reduce((total, item) => {
      return total + parseFloat(item.product.price) * item.quantity;
    }, 0);
  };

  if (cartItems.length === 0) {
    return (
      <div>
        <div className="flex justify-between items-center mb-6">
          <h2 className="text-3xl font-bold text-gray-800">Shopping Cart</h2>
          <button
            onClick={onClose}
            className="text-gray-600 hover:text-gray-800"
          >
            ← Back to Products
          </button>
        </div>
        <div className="bg-white rounded-lg shadow-md p-12 text-center">
          <p className="text-xl text-gray-600">Your cart is empty</p>
        </div>
      </div>
    );
  }

  return (
    <div>
      <div className="flex justify-between items-center mb-6">
        <h2 className="text-3xl font-bold text-gray-800">Shopping Cart</h2>
        <button
          onClick={onClose}
          className="text-gray-600 hover:text-gray-800"
        >
          ← Back to Products
        </button>
      </div>
      <div className="bg-white rounded-lg shadow-md overflow-hidden">
        <div className="divide-y divide-gray-200">
          {cartItems.map((item) => (
            <div key={item.id} className="p-6 flex items-center justify-between">
              <div className="flex-1">
                <h3 className="text-lg font-semibold text-gray-800">
                  {item.product.name}
                </h3>
                <p className="text-gray-600">
                  ${parseFloat(item.product.price).toFixed(2)} each
                </p>
              </div>
              <div className="flex items-center gap-4">
                <div className="flex items-center gap-2">
                  <button
                    onClick={() =>
                      onUpdate(item.id, Math.max(1, item.quantity - 1))
                    }
                    className="bg-gray-200 text-gray-700 w-8 h-8 rounded hover:bg-gray-300"
                  >
                    -
                  </button>
                  <span className="w-12 text-center font-medium">
                    {item.quantity}
                  </span>
                  <button
                    onClick={() => onUpdate(item.id, item.quantity + 1)}
                    disabled={item.quantity >= item.product.stock_quantity}
                    className="bg-gray-200 text-gray-700 w-8 h-8 rounded hover:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    +
                  </button>
                </div>
                <div className="text-right">
                  <p className="text-lg font-semibold text-gray-800">
                    ${(parseFloat(item.product.price) * item.quantity).toFixed(2)}
                  </p>
                </div>
                <button
                  onClick={() => onRemove(item.id)}
                  className="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600"
                >
                  Remove
                </button>
              </div>
            </div>
          ))}
        </div>
        <div className="p-6 bg-gray-50 border-t border-gray-200">
          <div className="flex justify-between items-center mb-4">
            <span className="text-xl font-bold text-gray-800">Total:</span>
            <span className="text-2xl font-bold text-blue-600">
              ${calculateTotal().toFixed(2)}
            </span>
          </div>
          <button
            onClick={onCheckout}
            className="w-full bg-green-500 text-white py-3 rounded-md hover:bg-green-600 font-semibold text-lg transition-colors"
          >
            Checkout & Place Order
          </button>
        </div>
      </div>
    </div>
  );
}

export default Cart;
