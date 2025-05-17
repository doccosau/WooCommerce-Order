"use client"

import Link from "next/link"
import { BarChart, Package, Users, ShoppingCart, Settings, LineChart } from "lucide-react"

type SidebarProps = {
  activePage: "dashboard" | "orders" | "products" | "customers" | "reports" | "settings"
}

export function Sidebar({ activePage }: SidebarProps) {
  return (
    <div className="hidden md:flex flex-col w-64 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700">
      <div className="flex items-center justify-between h-16 px-4 border-b border-gray-200 dark:border-gray-700">
        <div className="flex items-center">
          <Package className="h-6 w-6 text-blue-600 dark:text-blue-400" />
          <span className="ml-2 text-lg font-semibold">WooCenter</span>
        </div>
      </div>
      <div className="flex flex-col flex-1 overflow-y-auto">
        <nav className="flex-1 px-2 py-4 space-y-1">
          <Link
            href="/"
            className={`flex items-center px-2 py-2 text-sm font-medium rounded-md ${
              activePage === "dashboard"
                ? "bg-blue-50 text-blue-700 dark:bg-blue-900 dark:text-blue-200"
                : "text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white"
            }`}
          >
            <BarChart className="mr-3 h-5 w-5" />
            Dashboard
          </Link>
          <Link
            href="/orders"
            className={`flex items-center px-2 py-2 text-sm font-medium rounded-md ${
              activePage === "orders"
                ? "bg-blue-50 text-blue-700 dark:bg-blue-900 dark:text-blue-200"
                : "text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white"
            }`}
          >
            <ShoppingCart className="mr-3 h-5 w-5" />
            Đơn hàng
          </Link>
          <Link
            href="/products"
            className={`flex items-center px-2 py-2 text-sm font-medium rounded-md ${
              activePage === "products"
                ? "bg-blue-50 text-blue-700 dark:bg-blue-900 dark:text-blue-200"
                : "text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white"
            }`}
          >
            <Package className="mr-3 h-5 w-5" />
            Sản phẩm
          </Link>
          <Link
            href="/customers"
            className={`flex items-center px-2 py-2 text-sm font-medium rounded-md ${
              activePage === "customers"
                ? "bg-blue-50 text-blue-700 dark:bg-blue-900 dark:text-blue-200"
                : "text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white"
            }`}
          >
            <Users className="mr-3 h-5 w-5" />
            Khách hàng
          </Link>
          <Link
            href="/reports"
            className={`flex items-center px-2 py-2 text-sm font-medium rounded-md ${
              activePage === "reports"
                ? "bg-blue-50 text-blue-700 dark:bg-blue-900 dark:text-blue-200"
                : "text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white"
            }`}
          >
            <LineChart className="mr-3 h-5 w-5" />
            Báo cáo
          </Link>
          <Link
            href="/settings"
            className={`flex items-center px-2 py-2 text-sm font-medium rounded-md ${
              activePage === "settings"
                ? "bg-blue-50 text-blue-700 dark:bg-blue-900 dark:text-blue-200"
                : "text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white"
            }`}
          >
            <Settings className="mr-3 h-5 w-5" />
            Cài đặt
          </Link>
        </nav>
      </div>
    </div>
  )
}
