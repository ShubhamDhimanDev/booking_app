import React, { useState } from 'react';
import { Inertia } from '@inertiajs/inertia';

export default function PaymentGatewaySetting({ current, gateways }) {
  const [gateway, setGateway] = useState(current || gateways[0]);
  const [success, setSuccess] = useState(false);

  const handleSubmit = (e) => {
    e.preventDefault();
    Inertia.put(route('admin.payment-gateway.update'), { gateway }, {
      onSuccess: () => setSuccess(true),
    });
  };

  return (
    <div className="max-w-lg mx-auto mt-10">
      <h2 className="text-xl font-bold mb-4">Select Payment Gateway</h2>
      {success && <div className="bg-green-100 text-green-800 p-2 mb-4">Payment gateway updated.</div>}
      <form onSubmit={handleSubmit}>
        <div className="mb-4">
          <label className="block mb-2">Payment Gateway</label>
          <select value={gateway} onChange={e => setGateway(e.target.value)} className="border p-2 w-full">
            {gateways.map(g => (
              <option key={g} value={g}>{g.charAt(0).toUpperCase() + g.slice(1)}</option>
            ))}
          </select>
        </div>
        <button type="submit" className="bg-blue-600 text-white px-4 py-2 rounded">Save</button>
      </form>
    </div>
  );
}
