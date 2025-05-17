"use client"

import { Bar, BarChart, CartesianGrid, Legend, ResponsiveContainer, Tooltip, XAxis, YAxis } from "recharts"
import { Skeleton } from "@/components/ui/skeleton"

interface OrdersDetailChartProps {
  className?: string
  isLoading?: boolean
}

export function OrdersDetailChart({ className, isLoading = false }: OrdersDetailChartProps) {
  // Sample data for the orders detail chart
  const data = [
    { date: "01/05", "Đang xử lý": 3, "Hoàn thành": 8, "Đã hủy": 1 },
    { date: "02/05", "Đang xử lý": 4, "Hoàn thành": 10, "Đã hủy": 1 },
    { date: "03/05", "Đang xử lý": 5, "Hoàn thành": 12, "Đã hủy": 1 },
    { date: "04/05", "Đang xử lý": 3, "Hoàn thành": 10, "Đã hủy": 1 },
    { date: "05/05", "Đang xử lý": 4, "Hoàn thành": 15, "Đã hủy": 1 },
    { date: "06/05", "Đang xử lý": 6, "Hoàn thành": 18, "Đã hủy": 1 },
    { date: "07/05", "Đang xử lý": 5, "Hoàn thành": 16, "Đã hủy": 1 },
    { date: "08/05", "Đang xử lý": 4, "Hoàn thành": 13, "Đã hủy": 1 },
    { date: "09/05", "Đang xử lý": 5, "Hoàn thành": 14, "Đã hủy": 1 },
    { date: "10/05", "Đang xử lý": 6, "Hoàn thành": 17, "Đã hủy": 1 },
    { date: "11/05", "Đang xử lý": 7, "Hoàn thành": 20, "Đã hủy": 1 },
    { date: "12/05", "Đang xử lý": 8, "Hoàn thành": 21, "Đã hủy": 1 },
    { date: "13/05", "Đang xử lý": 6, "Hoàn thành": 19, "Đã hủy": 1 },
    { date: "14/05", "Đang xử lý": 5, "Hoàn thành": 16, "Đã hủy": 1 },
    { date: "15/05", "Đang xử lý": 6, "Hoàn thành": 18, "Đã hủy": 1 },
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
        <BarChart data={data}>
          <CartesianGrid strokeDasharray="3 3" />
          <XAxis dataKey="date" />
          <YAxis />
          <Tooltip />
          <Legend />
          <Bar dataKey="Đang xử lý" stackId="a" fill="#ffc658" />
          <Bar dataKey="Hoàn thành" stackId="a" fill="#82ca9d" />
          <Bar dataKey="Đã hủy" stackId="a" fill="#ff8042" />
        </BarChart>
      </ResponsiveContainer>
    </div>
  )
}
