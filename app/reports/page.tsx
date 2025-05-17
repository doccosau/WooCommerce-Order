"use client"

import { useState } from "react"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { Button } from "@/components/ui/button"
import { Download, Calendar, BarChart3 } from "lucide-react"
import { Sidebar } from "@/components/layout/sidebar"
import { TopNav } from "@/components/layout/top-nav"
import { RevenueChart } from "@/components/charts/revenue-chart"
import { OrdersChart } from "@/components/charts/orders-chart"
import Link from "next/link"

export default function ReportsPage() {
  const [selectedSite, setSelectedSite] = useState("all")
  const [selectedPeriod, setSelectedPeriod] = useState("month")

  return (
    <div className="flex min-h-screen bg-gray-50 dark:bg-gray-900">
      {/* Sidebar */}
      <Sidebar activePage="reports" />

      {/* Main content */}
      <div className="flex-1 flex flex-col">
        {/* Top navigation */}
        <TopNav selectedSite={selectedSite} setSelectedSite={setSelectedSite} />

        {/* Page content */}
        <main className="flex-1 overflow-y-auto bg-gray-50 dark:bg-gray-900 p-4 sm:p-6 lg:p-8">
          <div className="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
            <h1 className="text-2xl font-semibold text-gray-900 dark:text-white mb-4 md:mb-0">Báo cáo thống kê</h1>
            <div className="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2 w-full md:w-auto">
              <Select value={selectedPeriod} onValueChange={setSelectedPeriod}>
                <SelectTrigger className="w-[180px]">
                  <SelectValue placeholder="Chọn thời gian" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="week">Tuần này</SelectItem>
                  <SelectItem value="month">Tháng này</SelectItem>
                  <SelectItem value="quarter">Quý này</SelectItem>
                  <SelectItem value="year">Năm nay</SelectItem>
                </SelectContent>
              </Select>
              <Button variant="outline">
                <Calendar className="h-4 w-4 mr-2" />
                Tùy chỉnh
              </Button>
              <Link href="/reports/detailed">
                <Button variant="outline">
                  <BarChart3 className="h-4 w-4 mr-2" />
                  Báo cáo chi tiết
                </Button>
              </Link>
              <Button>
                <Download className="h-4 w-4 mr-2" />
                Xuất báo cáo
              </Button>
            </div>
          </div>

          <Tabs defaultValue="sales" className="w-full">
            <TabsList className="mb-4">
              <TabsTrigger value="sales">Doanh thu</TabsTrigger>
              <TabsTrigger value="orders">Đơn hàng</TabsTrigger>
              <TabsTrigger value="products">Sản phẩm</TabsTrigger>
              <TabsTrigger value="customers">Khách hàng</TabsTrigger>
            </TabsList>

            <TabsContent value="sales">
              <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <Card>
                  <CardHeader className="pb-2">
                    <CardDescription>Tổng doanh thu</CardDescription>
                  </CardHeader>
                  <CardContent>
                    <div className="text-2xl font-bold">152.430.000đ</div>
                    <p className="text-xs text-muted-foreground">
                      <span className="text-green-500">+12.5%</span> so với kỳ trước
                    </p>
                  </CardContent>
                </Card>
                <Card>
                  <CardHeader className="pb-2">
                    <CardDescription>Shop Thời Trang</CardDescription>
                  </CardHeader>
                  <CardContent>
                    <div className="text-2xl font-bold">58.430.000đ</div>
                    <p className="text-xs text-muted-foreground">
                      <span className="text-green-500">+8.2%</span> so với kỳ trước
                    </p>
                  </CardContent>
                </Card>
                <Card>
                  <CardHeader className="pb-2">
                    <CardDescription>Shop Điện Tử</CardDescription>
                  </CardHeader>
                  <CardContent>
                    <div className="text-2xl font-bold">65.750.000đ</div>
                    <p className="text-xs text-muted-foreground">
                      <span className="text-green-500">+15.3%</span> so với kỳ trước
                    </p>
                  </CardContent>
                </Card>
                <Card>
                  <CardHeader className="pb-2">
                    <CardDescription>Shop Mỹ Phẩm</CardDescription>
                  </CardHeader>
                  <CardContent>
                    <div className="text-2xl font-bold">28.250.000đ</div>
                    <p className="text-xs text-muted-foreground">
                      <span className="text-green-500">+10.8%</span> so với kỳ trước
                    </p>
                  </CardContent>
                </Card>
              </div>

              <Card className="mb-6">
                <CardHeader>
                  <CardTitle>Biểu đồ doanh thu</CardTitle>
                  <CardDescription>Doanh thu theo website trong kỳ</CardDescription>
                </CardHeader>
                <CardContent>
                  <RevenueChart className="h-[400px]" />
                </CardContent>
              </Card>

              <Card>
                <CardHeader>
                  <CardTitle>Phân tích doanh thu</CardTitle>
                  <CardDescription>Chi tiết doanh thu theo danh mục sản phẩm</CardDescription>
                </CardHeader>
                <CardContent>
                  <div className="overflow-x-auto">
                    <table className="w-full text-sm text-left">
                      <thead className="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-800 dark:text-gray-400">
                        <tr>
                          <th scope="col" className="px-6 py-3">
                            Danh mục
                          </th>
                          <th scope="col" className="px-6 py-3">
                            Doanh thu
                          </th>
                          <th scope="col" className="px-6 py-3">
                            Số đơn hàng
                          </th>
                          <th scope="col" className="px-6 py-3">
                            Tỷ lệ
                          </th>
                          <th scope="col" className="px-6 py-3">
                            So với kỳ trước
                          </th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr className="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                          <td className="px-6 py-4 font-medium">Thời trang nam</td>
                          <td className="px-6 py-4">35.250.000đ</td>
                          <td className="px-6 py-4">78</td>
                          <td className="px-6 py-4">23.1%</td>
                          <td className="px-6 py-4 text-green-500">+12.5%</td>
                        </tr>
                        <tr className="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                          <td className="px-6 py-4 font-medium">Thời trang nữ</td>
                          <td className="px-6 py-4">23.180.000đ</td>
                          <td className="px-6 py-4">65</td>
                          <td className="px-6 py-4">15.2%</td>
                          <td className="px-6 py-4 text-green-500">+8.3%</td>
                        </tr>
                        <tr className="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                          <td className="px-6 py-4 font-medium">Điện thoại</td>
                          <td className="px-6 py-4">42.350.000đ</td>
                          <td className="px-6 py-4">32</td>
                          <td className="px-6 py-4">27.8%</td>
                          <td className="px-6 py-4 text-green-500">+15.2%</td>
                        </tr>
                        <tr className="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                          <td className="px-6 py-4 font-medium">Phụ kiện điện tử</td>
                          <td className="px-6 py-4">23.400.000đ</td>
                          <td className="px-6 py-4">45</td>
                          <td className="px-6 py-4">15.4%</td>
                          <td className="px-6 py-4 text-green-500">+10.5%</td>
                        </tr>
                        <tr className="bg-white dark:bg-gray-800">
                          <td className="px-6 py-4 font-medium">Mỹ phẩm</td>
                          <td className="px-6 py-4">28.250.000đ</td>
                          <td className="px-6 py-4">85</td>
                          <td className="px-6 py-4">18.5%</td>
                          <td className="px-6 py-4 text-green-500">+10.8%</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </CardContent>
              </Card>
            </TabsContent>

            <TabsContent value="orders">
              <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <Card>
                  <CardHeader className="pb-2">
                    <CardDescription>Tổng đơn hàng</CardDescription>
                  </CardHeader>
                  <CardContent>
                    <div className="text-2xl font-bold">245</div>
                    <p className="text-xs text-muted-foreground">
                      <span className="text-green-500">+18.2%</span> so với kỳ trước
                    </p>
                  </CardContent>
                </Card>
                <Card>
                  <CardHeader className="pb-2">
                    <CardDescription>Đơn hoàn thành</CardDescription>
                  </CardHeader>
                  <CardContent>
                    <div className="text-2xl font-bold">198</div>
                    <p className="text-xs text-muted-foreground">
                      <span className="text-green-500">+15.5%</span> so với kỳ trước
                    </p>
                  </CardContent>
                </Card>
                <Card>
                  <CardHeader className="pb-2">
                    <CardDescription>Đơn đang xử lý</CardDescription>
                  </CardHeader>
                  <CardContent>
                    <div className="text-2xl font-bold">35</div>
                    <p className="text-xs text-muted-foreground">
                      <span className="text-green-500">+12.3%</span> so với kỳ trước
                    </p>
                  </CardContent>
                </Card>
                <Card>
                  <CardHeader className="pb-2">
                    <CardDescription>Đơn hủy</CardDescription>
                  </CardHeader>
                  <CardContent>
                    <div className="text-2xl font-bold">12</div>
                    <p className="text-xs text-muted-foreground">
                      <span className="text-red-500">+2.8%</span> so với kỳ trước
                    </p>
                  </CardContent>
                </Card>
              </div>

              <Card className="mb-6">
                <CardHeader>
                  <CardTitle>Biểu đồ đơn hàng</CardTitle>
                  <CardDescription>Số lượng đơn hàng theo ngày</CardDescription>
                </CardHeader>
                <CardContent>
                  <OrdersChart className="h-[400px]" />
                </CardContent>
              </Card>
            </TabsContent>

            {/* Các tab khác tương tự */}
          </Tabs>
        </main>
      </div>
    </div>
  )
}
