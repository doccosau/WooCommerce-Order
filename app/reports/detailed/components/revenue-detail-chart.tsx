"use client"

import { Area, AreaChart, CartesianGrid, Legend, ResponsiveContainer, Tooltip, XAxis, YAxis } from "recharts"
import { Skeleton } from "@/components/ui/skeleton"

interface RevenueDetailChartProps {
  className?: string
  isLoading?: boolean
}

export function RevenueDetailChart({ className, isLoading = false }: RevenueDetailChartProps) {
  // Sample data for the revenue detail chart
  const data = [
    { date: "01/05", "Shop Thời Trang": 4500000, "Shop Điện Tử": 6200000, "Shop Mỹ Phẩm": 2800000 },
    { date: "02/05", "Shop Thời Trang": 4800000, "Shop Điện Tử": 5800000, "Shop Mỹ Phẩm": 3100000 },
    { date: "03/05", "Shop Thời Trang": 5200000, "Shop Điện Tử": 6500000, "Shop Mỹ Phẩm": 2900000 },
    { date: "04/05", "Shop Thời Trang": 4900000, "Shop Điện Tử": 6100000, "Shop Mỹ Phẩm": 2700000 },
    { date: "05/05", "Shop Thời Trang": 5500000, "Shop Điện Tử": 7200000, "Shop Mỹ Phẩm": 3200000 },
    { date: "06/05", "Shop Thời Trang": 6100000, "Shop Điện Tử": 7800000, "Shop Mỹ Phẩm": 3500000 },
    { date: "07/05", "Shop Thời Trang": 5800000, "Shop Điện Tử": 7500000, "Shop Mỹ Phẩm": 3300000 },
    { date: "08/05", "Shop Thời Trang": 5400000, "Shop Điện Tử": 6800000, "Shop Mỹ Phẩm": 3000000 },
    { date: "09/05", "Shop Thời Trang": 5700000, "Shop Điện Tử": 7100000, "Shop Mỹ Phẩm": 3200000 },
    { date: "10/05", "Shop Thời Trang": 6200000, "Shop Điện Tử": 7900000, "Shop Mỹ Phẩm": 3600000 },
    { date: "11/05", "Shop Thời Trang": 6500000, "Shop Điện Tử": 8200000, "Shop Mỹ Phẩm": 3800000 },
    { date: "12/05", "Shop Thời Trang": 6800000, "Shop Điện Tử": 8500000, "Shop Mỹ Phẩm": 4000000 },
    { date: "13/05", "Shop Thời Trang": 6300000, "Shop Điện Tử": 8100000, "Shop Mỹ Phẩm": 3700000 },
    { date: "14/05", "Shop Thời Trang": 5900000, "Shop Điện Tử": 7600000, "Shop Mỹ Phẩm": 3400000 },
    { date: "15/05", "Shop Thời Trang": 6100000, "Shop Điện Tử": 7800000, "Shop Mỹ Phẩm": 3500000 },
  ]

  if (isLoading) {
    return (
      <div className={className}>
        <Skeleton className="w-full h-full" />
      </div>
    )
  }

  return (
    <div className={className}>
      <ResponsiveContainer width="100%" height="100%">
        <AreaChart data={data}>
          <CartesianGrid strokeDasharray="3 3" />
          <XAxis dataKey="date" />
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
          <Area type="monotone" dataKey="Shop Thời Trang" stackId="1" stroke="#8884d8" fill="#8884d8" />
          <Area type="monotone" dataKey="Shop Điện Tử" stackId="1" stroke="#82ca9d" fill="#82ca9d" />
          <Area type="monotone" dataKey="Shop Mỹ Phẩm" stackId="1" stroke="#ffc658" fill="#ffc658" />
        </AreaChart>
      </ResponsiveContainer>
    </div>
  )
}
