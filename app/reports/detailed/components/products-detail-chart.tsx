"use client"

import { Cell, Legend, Pie, PieChart, ResponsiveContainer, Tooltip } from "recharts"
import { Skeleton } from "@/components/ui/skeleton"

interface ProductsDetailChartProps {
  className?: string
  isLoading?: boolean
}

export function ProductsDetailChart({ className, isLoading = false }: ProductsDetailChartProps) {
  // Sample data for the products detail chart
  const data = [
    { name: "Thời trang nam", value: 35 },
    { name: "Thời trang nữ", value: 25 },
    { name: "Điện thoại", value: 15 },
    { name: "Phụ kiện điện tử", value: 10 },
    { name: "Mỹ phẩm", value: 15 },
  ]

  const COLORS = ["#0088FE", "#00C49F", "#FFBB28", "#FF8042", "#8884D8"]

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
        <PieChart>
          <Pie
            data={data}
            cx="50%"
            cy="50%"
            labelLine={false}
            outerRadius={120}
            fill="#8884d8"
            dataKey="value"
            label={({ name, percent }) => `${name} ${(percent * 100).toFixed(0)}%`}
          >
            {data.map((entry, index) => (
              <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
            ))}
          </Pie>
          <Tooltip formatter={(value) => `${value} sản phẩm`} />
          <Legend />
        </PieChart>
      </ResponsiveContainer>
    </div>
  )
}
