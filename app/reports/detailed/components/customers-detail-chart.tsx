"use client"

import { CartesianGrid, Legend, Line, LineChart, ResponsiveContainer, Tooltip, XAxis, YAxis } from "recharts"
import { Skeleton } from "@/components/ui/skeleton"

interface CustomersDetailChartProps {
  className?: string
  isLoading?: boolean
}

export function CustomersDetailChart({ className, isLoading = false }: CustomersDetailChartProps) {
  // Sample data for the customers detail chart
  const data = [
    { date: "01/05", "Khách hàng mới": 5, "Khách hàng quay lại": 8 },
    { date: "02/05", "Khách hàng mới": 7, "Khách hàng quay lại": 10 },
    { date: "03/05", "Khách hàng mới": 8, "Khách hàng quay lại": 12 },
    { date: "04/05", "Khách hàng mới": 6, "Khách hàng quay lại": 9 },
    { date: "05/05", "Khách hàng mới": 9, "Khách hàng quay lại": 13 },
    { date: "06/05", "Khách hàng mới": 11, "Khách hàng quay lại": 15 },
    { date: "07/05", "Khách hàng mới": 10, "Khách hàng quay lại": 14 },
    { date: "08/05", "Khách hàng mới": 8, "Khách hàng quay lại": 12 },
    { date: "09/05", "Khách hàng mới": 9, "Khách hàng quay lại": 13 },
    { date: "10/05", "Khách hàng mới": 12, "Khách hàng quay lại": 16 },
    { date: "11/05", "Khách hàng mới": 14, "Khách hàng quay lại": 18 },
    { date: "12/05", "Khách hàng mới": 15, "Khách hàng quay lại": 19 },
    { date: "13/05", "Khách hàng mới": 13, "Khách hàng quay lại": 17 },
    { date: "14/05", "Khách hàng mới": 10, "Khách hàng quay lại": 14 },
    { date: "15/05", "Khách hàng mới": 12, "Khách hàng quay lại": 16 },
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
        <LineChart data={data}>
          <CartesianGrid strokeDasharray="3 3" />
          <XAxis dataKey="date" />
          <YAxis />
          <Tooltip />
          <Legend />
          <Line type="monotone" dataKey="Khách hàng mới" stroke="#8884d8" activeDot={{ r: 8 }} />
          <Line type="monotone" dataKey="Khách hàng quay lại" stroke="#82ca9d" activeDot={{ r: 8 }} />
        </LineChart>
      </ResponsiveContainer>
    </div>
  )
}
