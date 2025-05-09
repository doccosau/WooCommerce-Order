"use client"

import { Bar, BarChart, CartesianGrid, Legend, ResponsiveContainer, Tooltip, XAxis, YAxis } from "recharts"

interface RevenueChartProps {
  className?: string
}

export function RevenueChart({ className }: RevenueChartProps) {
  // Sample data for the revenue chart
  const data = [
    {
      name: "Tuần 1",
      "Shop Thời Trang": 25000000,
      "Shop Điện Tử": 35000000,
      "Shop Mỹ Phẩm": 15000000,
    },
    {
      name: "Tuần 2",
      "Shop Thời Trang": 30000000,
      "Shop Điện Tử": 28000000,
      "Shop Mỹ Phẩm": 18000000,
    },
    {
      name: "Tuần 3",
      "Shop Thời Trang": 27000000,
      "Shop Điện Tử": 32000000,
      "Shop Mỹ Phẩm": 20000000,
    },
    {
      name: "Tuần 4",
      "Shop Thời Trang": 32000000,
      "Shop Điện Tử": 38000000,
      "Shop Mỹ Phẩm": 22000000,
    },
  ]

  return (
    <div className={className}>
      <ResponsiveContainer width="100%" height="100%">
        <BarChart data={data}>
          <CartesianGrid strokeDasharray="3 3" />
          <XAxis dataKey="name" />
          <YAxis
            tickFormatter={(value) =>
              new Intl.NumberFormat("vi-VN", {
                notation: "compact",
                compactDisplay: "short",
              }).format(value)
            }
          />
          <Tooltip
            formatter={(value: number) =>
              new Intl.NumberFormat("vi-VN", {
                style: "currency",
                currency: "VND",
                maximumFractionDigits: 0,
              }).format(value)
            }
          />
          <Legend />
          <Bar dataKey="Shop Thời Trang" fill="#8884d8" />
          <Bar dataKey="Shop Điện Tử" fill="#82ca9d" />
          <Bar dataKey="Shop Mỹ Phẩm" fill="#ffc658" />
        </BarChart>
      </ResponsiveContainer>
    </div>
  )
}
