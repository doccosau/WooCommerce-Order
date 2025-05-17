"use client"

import { useState, useEffect } from "react"
import { usePathname } from "next/navigation"
import { Bell, Moon, Sun } from "lucide-react"
import { Button } from "@/components/ui/button"
import { UserMenu } from "@/components/auth/user-menu"
import { useTheme } from "next-themes"

export function TopNav() {
  const pathname = usePathname()
  const [mounted, setMounted] = useState(false)
  const { theme, setTheme } = useTheme()

  // Đảm bảo component được render ở client side
  useEffect(() => {
    setMounted(true)
  }, [])

  // Lấy tiêu đề trang dựa trên đường dẫn
  const getPageTitle = () => {
    switch (pathname) {
      case "/":
        return "Dashboard"
      case "/orders":
        return "Đơn hàng"
      case "/products":
        return "Sản phẩm"
      case "/customers":
        return "Khách hàng"
      case "/reports":
        return "Báo cáo"
      case "/websites":
        return "Websites"
      case "/user-management":
        return "Quản lý người dùng"
      case "/settings":
        return "Cài đặt"
      default:
        if (pathname.startsWith("/orders/")) return "Chi tiết đơn hàng"
        if (pathname.startsWith("/products/")) return "Chi tiết sản phẩm"
        if (pathname.startsWith("/customers/")) return "Chi tiết khách hàng"
        return "WooCenter"
    }
  }

  return (
    <div className="sticky top-0 z-30 flex h-16 items-center justify-between border-b bg-white px-4 dark:bg-gray-800 dark:border-gray-700">
      <h1 className="text-xl font-semibold">{getPageTitle()}</h1>
      <div className="flex items-center space-x-2">
        <Button variant="ghost" size="icon">
          <Bell className="h-5 w-5" />
        </Button>
        {mounted && (
          <Button variant="ghost" size="icon" onClick={() => setTheme(theme === "dark" ? "light" : "dark")}>
            {theme === "dark" ? <Sun className="h-5 w-5" /> : <Moon className="h-5 w-5" />}
          </Button>
        )}
        <UserMenu />
      </div>
    </div>
  )
}
