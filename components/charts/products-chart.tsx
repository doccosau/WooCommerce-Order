"use client"

import { Cell, Legend, Pie, PieChart, ResponsiveContainer, Tooltip } from "recharts"

interface ProductsChartProps {
  className?: string
}

export function ProductsChart({ className }: ProductsChartProps) {
  // Sample data for the products chart
  const data = [
    { name: "Thời trang nam", value: 35 },
    { name: "Thời trang nữ", value: 25 },
    { name: "Điện thoại", value: 15 },
    { name: "Phụ kiện điện tử", value: 10 },
    { name: "Mỹ phẩm", value: 15 },
  ]

  const COLORS = ["#0088FE", "#00C49F", "#FFBB28", "#FF8042", "#8884D8"]

  return (
    <div className={className}>
      <ResponsiveContainer width="100%" height="100%">
        <PieChart>
          <Pie
            data={data}
            cx="50%"
            cy="50%"
            labelLine={false}
            outerRadius={80}
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
