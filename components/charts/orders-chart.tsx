"use client"

import { CartesianGrid, Legend, Line, LineChart, ResponsiveContainer, Tooltip, XAxis, YAxis } from "recharts"

interface OrdersChartProps {
  className?: string
}

export function OrdersChart({ className }: OrdersChartProps) {
  // Sample data for the orders chart
  const data = [
    { name: "01/05", orders: 12 },
    { name: "02/05", orders: 15 },
    { name: "03/05", orders: 18 },
    { name: "04/05", orders: 14 },
    { name: "05/05", orders: 20 },
    { name: "06/05", orders: 25 },
    { name: "07/05", orders: 22 },
    { name: "08/05", orders: 18 },
    { name: "09/05", orders: 20 },
    { name: "10/05", orders: 24 },
    { name: "11/05", orders: 28 },
    { name: "12/05", orders: 30 },
    { name: "13/05", orders: 26 },
    { name: "14/05", orders: 22 },
    { name: "15/05", orders: 25 },
  ]

  return (
    <div className={className}>
      <ResponsiveContainer width="100%" height="100%">
        <LineChart data={data}>
          <CartesianGrid strokeDasharray="3 3" />
          <XAxis dataKey="name" />
          <YAxis />
          <Tooltip />
          <Legend />
          <Line type="monotone" dataKey="orders" stroke="#8884d8" activeDot={{ r: 8 }} name="Số đơn hàng" />
        </LineChart>
      </ResponsiveContainer>
    </div>
  )
}
