"use client"

import { useState } from "react"
import {
  PieChart,
  ArrowUpRight,
  Users,
  ShoppingCart,
  Bell,
  Settings,
  LogOut,
  Filter,
  Download,
  RefreshCw,
} from "lucide-react"
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar"
import { Badge } from "@/components/ui/badge"
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { Progress } from "@/components/ui/progress"
import { RevenueChart } from "@/components/charts/revenue-chart"
import { OrdersChart } from "@/components/charts/orders-chart"
import { Sidebar } from "@/components/sidebar"

export default function Dashboard() {
  const [selectedSite, setSelectedSite] = useState("all")
  const [syncStatus, setSyncStatus] = useState("Đã đồng bộ lúc 15:30")
  const [isLoading, setIsLoading] = useState(false)

  const handleSync = () => {
    setIsLoading(true)
    // Giả lập đồng bộ dữ liệu
    setTimeout(() => {
      setIsLoading(false)
      setSyncStatus("Đã đồng bộ lúc " + new Date().getHours() + ":" + new Date().getMinutes())
    }, 2000)
  }

  return (
    <div className="flex min-h-screen bg-gray-50 dark:bg-gray-900">
      {/* Sidebar */}
      <Sidebar activePage="dashboard" />

      {/* Main content */}
      <div className="flex-1 flex flex-col">
        {/* Top navigation */}
        <header className="bg-white dark:bg-gray-800 shadow-sm z-10">
          <div className="px-4 sm:px-6 lg:px-8">
            <div className="flex items-center justify-between h-16">
              <div className="flex">
                <div className="flex-shrink-0 flex items-center">
                  <Select value={selectedSite} onValueChange={setSelectedSite}>
                    <SelectTrigger className="w-[180px] border-none shadow-none focus:ring-0">
                      <SelectValue placeholder="Chọn website" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="all">Tất cả website</SelectItem>
                      <SelectItem value="site1">Shop Thời Trang</SelectItem>
                      <SelectItem value="site2">Shop Điện Tử</SelectItem>
                      <SelectItem value="site3">Shop Mỹ Phẩm</SelectItem>
                    </SelectContent>
                  </Select>
                </div>
              </div>
              <div className="flex items-center space-x-4">
                <div className="relative">
                  <Button variant="outline" size="icon" className="relative">
                    <Bell className="h-5 w-5" />
                    <span className="absolute top-0 right-0 h-2 w-2 rounded-full bg-red-500"></span>
                  </Button>
                </div>
                <DropdownMenu>
                  <DropdownMenuTrigger asChild>
                    <Button variant="ghost" className="relative h-8 w-8 rounded-full">
                      <Avatar className="h-8 w-8">
                        <AvatarImage src="/placeholder.svg?height=32&width=32" alt="Avatar" />
                        <AvatarFallback>AD</AvatarFallback>
                      </Avatar>
                    </Button>
                  </DropdownMenuTrigger>
                  <DropdownMenuContent align="end">
                    <DropdownMenuLabel>Tài khoản của tôi</DropdownMenuLabel>
                    <DropdownMenuSeparator />
                    <DropdownMenuItem>
                      <Settings className="mr-2 h-4 w-4" />
                      <span>Cài đặt</span>
                    </DropdownMenuItem>
                    <DropdownMenuItem>
                      <LogOut className="mr-2 h-4 w-4" />
                      <span>Đăng xuất</span>
                    </DropdownMenuItem>
                  </DropdownMenuContent>
                </DropdownMenu>
              </div>
            </div>
          </div>
        </header>

        {/* Page content */}
        <main className="flex-1 overflow-y-auto bg-gray-50 dark:bg-gray-900 p-4 sm:p-6 lg:p-8">
          <div className="flex justify-between items-center mb-6">
            <h1 className="text-2xl font-semibold text-gray-900 dark:text-white">Dashboard</h1>
            <div className="flex items-center space-x-2">
              <span className="text-sm text-gray-500 dark:text-gray-400">{syncStatus}</span>
              <Button variant="outline" size="sm" onClick={handleSync} disabled={isLoading}>
                {isLoading ? (
                  <RefreshCw className="h-4 w-4 mr-2 animate-spin" />
                ) : (
                  <RefreshCw className="h-4 w-4 mr-2" />
                )}
                Đồng bộ dữ liệu
              </Button>
            </div>
          </div>

          {/* Stats cards */}
          <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <Card>
              <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle className="text-sm font-medium">Tổng doanh thu</CardTitle>
                <ArrowUpRight className="h-4 w-4 text-green-500" />
              </CardHeader>
              <CardContent>
                <div className="text-2xl font-bold">152.430.000đ</div>
                <p className="text-xs text-muted-foreground">
                  <span className="text-green-500">+12.5%</span> so với tháng trước
                </p>
              </CardContent>
            </Card>
            <Card>
              <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle className="text-sm font-medium">Đơn hàng mới</CardTitle>
                <ShoppingCart className="h-4 w-4 text-blue-500" />
              </CardHeader>
              <CardContent>
                <div className="text-2xl font-bold">245</div>
                <p className="text-xs text-muted-foreground">
                  <span className="text-green-500">+18.2%</span> so với tháng trước
                </p>
              </CardContent>
            </Card>
            <Card>
              <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle className="text-sm font-medium">Khách hàng mới</CardTitle>
                <Users className="h-4 w-4 text-violet-500" />
              </CardHeader>
              <CardContent>
                <div className="text-2xl font-bold">132</div>
                <p className="text-xs text-muted-foreground">
                  <span className="text-green-500">+8.3%</span> so với tháng trước
                </p>
              </CardContent>
            </Card>
            <Card>
              <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle className="text-sm font-medium">Tỷ lệ chuyển đổi</CardTitle>
                <PieChart className="h-4 w-4 text-orange-500" />
              </CardHeader>
              <CardContent>
                <div className="text-2xl font-bold">3.2%</div>
                <p className="text-xs text-muted-foreground">
                  <span className="text-red-500">-0.5%</span> so với tháng trước
                </p>
              </CardContent>
            </Card>
          </div>

          {/* Charts */}
          <div className="grid grid-cols-1 gap-4 mt-6 lg:grid-cols-2">
            <Card>
              <CardHeader>
                <CardTitle>Doanh thu theo website</CardTitle>
                <CardDescription>Thống kê doanh thu 30 ngày qua</CardDescription>
              </CardHeader>
              <CardContent>
                <RevenueChart className="aspect-[4/3]" />
              </CardContent>
            </Card>
            <Card>
              <CardHeader>
                <CardTitle>Đơn hàng theo ngày</CardTitle>
                <CardDescription>Số lượng đơn hàng 30 ngày qua</CardDescription>
              </CardHeader>
              <CardContent>
                <OrdersChart className="aspect-[4/3]" />
              </CardContent>
            </Card>
          </div>

          {/* Recent orders */}
          <div className="mt-6">
            <Card>
              <CardHeader className="flex flex-row items-center justify-between">
                <div>
                  <CardTitle>Đơn hàng gần đây</CardTitle>
                  <CardDescription>Danh sách 10 đơn hàng mới nhất</CardDescription>
                </div>
                <Button variant="outline" size="sm">
                  <Filter className="h-4 w-4 mr-2" />
                  Lọc
                </Button>
              </CardHeader>
              <CardContent>
                <div className="overflow-x-auto">
                  <table className="w-full text-sm text-left">
                    <thead className="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-800 dark:text-gray-400">
                      <tr>
                        <th scope="col" className="px-6 py-3">
                          Mã đơn
                        </th>
                        <th scope="col" className="px-6 py-3">
                          Khách hàng
                        </th>
                        <th scope="col" className="px-6 py-3">
                          Website
                        </th>
                        <th scope="col" className="px-6 py-3">
                          Tổng tiền
                        </th>
                        <th scope="col" className="px-6 py-3">
                          Trạng thái
                        </th>
                        <th scope="col" className="px-6 py-3">
                          Ngày tạo
                        </th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr className="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                        <td className="px-6 py-4 font-medium">#WC-1234</td>
                        <td className="px-6 py-4">Nguyễn Văn A</td>
                        <td className="px-6 py-4">Shop Thời Trang</td>
                        <td className="px-6 py-4">1.250.000đ</td>
                        <td className="px-6 py-4">
                          <Badge className="bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                            Hoàn thành
                          </Badge>
                        </td>
                        <td className="px-6 py-4">15/05/2023</td>
                      </tr>
                      <tr className="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                        <td className="px-6 py-4 font-medium">#WC-1233</td>
                        <td className="px-6 py-4">Trần Thị B</td>
                        <td className="px-6 py-4">Shop Điện Tử</td>
                        <td className="px-6 py-4">3.450.000đ</td>
                        <td className="px-6 py-4">
                          <Badge className="bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                            Đang giao
                          </Badge>
                        </td>
                        <td className="px-6 py-4">15/05/2023</td>
                      </tr>
                      <tr className="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                        <td className="px-6 py-4 font-medium">#WC-1232</td>
                        <td className="px-6 py-4">Lê Văn C</td>
                        <td className="px-6 py-4">Shop Mỹ Phẩm</td>
                        <td className="px-6 py-4">850.000đ</td>
                        <td className="px-6 py-4">
                          <Badge className="bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">
                            Đang xử lý
                          </Badge>
                        </td>
                        <td className="px-6 py-4">14/05/2023</td>
                      </tr>
                      <tr className="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                        <td className="px-6 py-4 font-medium">#WC-1231</td>
                        <td className="px-6 py-4">Phạm Thị D</td>
                        <td className="px-6 py-4">Shop Thời Trang</td>
                        <td className="px-6 py-4">1.750.000đ</td>
                        <td className="px-6 py-4">
                          <Badge className="bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300">Đã hủy</Badge>
                        </td>
                        <td className="px-6 py-4">14/05/2023</td>
                      </tr>
                      <tr className="bg-white dark:bg-gray-800">
                        <td className="px-6 py-4 font-medium">#WC-1230</td>
                        <td className="px-6 py-4">Hoàng Văn E</td>
                        <td className="px-6 py-4">Shop Điện Tử</td>
                        <td className="px-6 py-4">5.250.000đ</td>
                        <td className="px-6 py-4">
                          <Badge className="bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                            Hoàn thành
                          </Badge>
                        </td>
                        <td className="px-6 py-4">13/05/2023</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </CardContent>
              <CardFooter className="flex justify-between">
                <Button variant="outline" size="sm">
                  Trước
                </Button>
                <Button variant="outline" size="sm">
                  Tiếp
                </Button>
              </CardFooter>
            </Card>
          </div>

          {/* Product inventory */}
          <div className="mt-6">
            <Card>
              <CardHeader className="flex flex-row items-center justify-between">
                <div>
                  <CardTitle>Tồn kho sản phẩm</CardTitle>
                  <CardDescription>Sản phẩm sắp hết hàng</CardDescription>
                </div>
                <Button variant="outline" size="sm">
                  <Download className="h-4 w-4 mr-2" />
                  Xuất báo cáo
                </Button>
              </CardHeader>
              <CardContent>
                <div className="space-y-4">
                  <div className="flex items-center">
                    <div className="w-[30%]">
                      <div className="font-medium">Áo thun nam basic</div>
                      <div className="text-sm text-gray-500">SKU: SP-001</div>
                    </div>
                    <div className="w-[40%] px-4">
                      <Progress value={15} className="h-2" />
                    </div>
                    <div className="w-[30%] text-right">
                      <div className="font-medium">3 sản phẩm</div>
                      <div className="text-sm text-gray-500">Shop Thời Trang</div>
                    </div>
                  </div>
                  <div className="flex items-center">
                    <div className="w-[30%]">
                      <div className="font-medium">Tai nghe bluetooth</div>
                      <div className="text-sm text-gray-500">SKU: SP-245</div>
                    </div>
                    <div className="w-[40%] px-4">
                      <Progress value={10} className="h-2" />
                    </div>
                    <div className="w-[30%] text-right">
                      <div className="font-medium">2 sản phẩm</div>
                      <div className="text-sm text-gray-500">Shop Điện Tử</div>
                    </div>
                  </div>
                  <div className="flex items-center">
                    <div className="w-[30%]">
                      <div className="font-medium">Kem dưỡng da</div>
                      <div className="text-sm text-gray-500">SKU: SP-178</div>
                    </div>
                    <div className="w-[40%] px-4">
                      <Progress value={25} className="h-2" />
                    </div>
                    <div className="w-[30%] text-right">
                      <div className="font-medium">5 sản phẩm</div>
                      <div className="text-sm text-gray-500">Shop Mỹ Phẩm</div>
                    </div>
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>
        </main>
      </div>
    </div>
  )
}
