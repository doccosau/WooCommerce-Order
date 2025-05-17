"use client"

import { CartesianGrid, Legend, Line, LineChart, ResponsiveContainer, Tooltip, XAxis, YAxis } from "recharts"

interface CustomersChartProps {
  className?: string
}

export function CustomersChart({ className }: CustomersChartProps) {
  // Sample data for the customers chart
  const data = [
    { name: "Tuần 1", customers: 25 },
    { name: "Tuần 2", customers: 32 },
    { name: "Tuần 3", customers: 28 },
    { name: "Tuần 4", customers: 47 },
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
          <Line type="monotone" dataKey="customers" stroke="#8884d8" activeDot={{ r: 8 }} name="Khách hàng mới" />
        </LineChart>
      </ResponsiveContainer>
    </div>
  )
}
