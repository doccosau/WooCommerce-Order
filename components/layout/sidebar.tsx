"use client"

import type React from "react"

import { useState, useEffect } from "react"
import Link from "next/link"
import { usePathname } from "next/navigation"
import { createClient } from "@/lib/supabase/client"
import {
  BarChart,
  Package,
  Users,
  ShoppingCart,
  Settings,
  LineChart,
  Globe,
  Shield,
  Menu,
  X,
  RefreshCw,
} from "lucide-react"
import { Button } from "@/components/ui/button"
import { cn } from "@/lib/utils"

interface NavItem {
  href: string
  label: string
  icon: React.ReactNode
  permission?: string
}

export function Sidebar() {
  const pathname = usePathname()
  const [userPermissions, setUserPermissions] = useState<string[]>([])
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false)

  const supabase = createClient()

  useEffect(() => {
    const fetchUserPermissions = async () => {
      try {
        const {
          data: { session },
        } = await supabase.auth.getSession()

        if (session?.user) {
          // Lấy role_id của người dùng
          const { data: userProfile } = await supabase
            .from("user_profiles")
            .select("role_id")
            .eq("id", session.user.id)
            .single()

          if (userProfile?.role_id) {
            // Lấy danh sách quyền của role
            const { data: rolePermissions } = await supabase
              .from("role_permissions")
              .select(`
                permissions:permission_id (
                  name
                )
              `)
              .eq("role_id", userProfile.role_id)

            if (rolePermissions) {
              const permissions = rolePermissions.map((item) => item.permissions.name)
              setUserPermissions(permissions)
            }
          }
        }
      } catch (error) {
        console.error("Error fetching user permissions:", error)
      }
    }

    fetchUserPermissions()
  }, [])

  const navItems: NavItem[] = [
    {
      href: "/",
      label: "Dashboard",
      icon: <BarChart className="mr-3 h-5 w-5" />,
      permission: "view_dashboard",
    },
    {
      href: "/orders",
      label: "Đơn hàng",
      icon: <ShoppingCart className="mr-3 h-5 w-5" />,
      permission: "view_orders",
    },
    {
      href: "/products",
      label: "Sản phẩm",
      icon: <Package className="mr-3 h-5 w-5" />,
      permission: "view_products",
    },
    {
      href: "/customers",
      label: "Khách hàng",
      icon: <Users className="mr-3 h-5 w-5" />,
      permission: "view_customers",
    },
    {
      href: "/reports",
      label: "Báo cáo",
      icon: <LineChart className="mr-3 h-5 w-5" />,
      permission: "view_reports",
    },
    {
      href: "/websites",
      label: "Websites",
      icon: <Globe className="mr-3 h-5 w-5" />,
      permission: "manage_websites",
    },
    {
      href: "/sync",
      label: "Đồng bộ dữ liệu",
      icon: <RefreshCw className="mr-3 h-5 w-5" />,
      permission: "sync_data",
    },
    {
      href: "/user-management",
      label: "Quản lý người dùng",
      icon: <Shield className="mr-3 h-5 w-5" />,
      permission: "manage_users",
    },
    {
      href: "/settings",
      label: "Cài đặt",
      icon: <Settings className="mr-3 h-5 w-5" />,
      permission: "manage_settings",
    },
  ]

  // Lọc các mục menu dựa trên quyền của người dùng
  const filteredNavItems = navItems.filter((item) => {
    // Nếu không yêu cầu quyền cụ thể, hiển thị mục đó
    if (!item.permission) return true

    // Nếu người dùng có quyền cụ thể, hiển thị mục đó
    return userPermissions.includes(item.permission)
  })

  return (
    <>
      {/* Mobile menu button */}
      <div className="md:hidden fixed top-4 left-4 z-50">
        <Button variant="outline" size="icon" onClick={() => setIsMobileMenuOpen(!isMobileMenuOpen)}>
          {isMobileMenuOpen ? <X className="h-5 w-5" /> : <Menu className="h-5 w-5" />}
        </Button>
      </div>

      {/* Sidebar for desktop */}
      <div
        className={cn(
          "fixed inset-y-0 left-0 z-40 w-64 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 transform transition-transform duration-200 ease-in-out md:translate-x-0",
          isMobileMenuOpen ? "translate-x-0" : "-translate-x-full",
        )}
      >
        <div className="flex items-center justify-between h-16 px-4 border-b border-gray-200 dark:border-gray-700">
          <div className="flex items-center">
            <Package className="h-6 w-6 text-blue-600 dark:text-blue-400" />
            <span className="ml-2 text-lg font-semibold">WooCenter</span>
          </div>
          <Button variant="ghost" size="icon" className="md:hidden" onClick={() => setIsMobileMenuOpen(false)}>
            <X className="h-5 w-5" />
          </Button>
        </div>
        <div className="flex flex-col flex-1 overflow-y-auto">
          <nav className="flex-1 px-2 py-4 space-y-1">
            {filteredNavItems.map((item) => (
              <Link
                key={item.href}
                href={item.href}
                className={cn(
                  "flex items-center px-2 py-2 text-sm font-medium rounded-md",
                  pathname === item.href
                    ? "bg-blue-50 text-blue-700 dark:bg-blue-900 dark:text-blue-200"
                    : "text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white",
                )}
                onClick={() => setIsMobileMenuOpen(false)}
              >
                {item.icon}
                {item.label}
              </Link>
            ))}
          </nav>
        </div>
      </div>

      {/* Overlay for mobile */}
      {isMobileMenuOpen && (
        <div
          className="fixed inset-0 bg-black bg-opacity-50 z-30 md:hidden"
          onClick={() => setIsMobileMenuOpen(false)}
        />
      )}
    </>
  )
}
