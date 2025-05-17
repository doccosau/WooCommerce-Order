"use client"

import { useState } from "react"
import { Search, Filter, Eye, Edit, RefreshCw } from "lucide-react"
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { Badge } from "@/components/ui/badge"
import { Input } from "@/components/ui/input"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs"
import { Sidebar } from "@/components/sidebar"
import { TopNav } from "@/components/top-nav"

export default function OrdersPage() {
  const [selectedSite, setSelectedSite] = useState("all")
  const [selectedStatus, setSelectedStatus] = useState("all")
  const [searchQuery, setSearchQuery] = useState("")

  return (
    <div className="flex min-h-screen bg-gray-50 dark:bg-gray-900">
      {/* Sidebar */}
      <Sidebar activePage="orders" />

      {/* Main content */}
      <div className="flex-1 flex flex-col">
        {/* Top navigation */}
        <TopNav selectedSite={selectedSite} setSelectedSite={setSelectedSite} />

        {/* Page content */}
        <main className="flex-1 overflow-y-auto bg-gray-50 dark:bg-gray-900 p-4 sm:p-6 lg:p-8">
          <div className="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
            <h1 className="text-2xl font-semibold text-gray-900 dark:text-white mb-4 md:mb-0">Quản lý đơn hàng</h1>
            <div className="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2 w-full md:w-auto">
              <div className="relative w-full md:w-64">
                <Search className="absolute left-2.5 top-2.5 h-4 w-4 text-gray-500 dark:text-gray-400" />
                <Input
                  type="search"
                  placeholder="Tìm kiếm đơn hàng..."
                  className="pl-8"
                  value={searchQuery}
                  onChange={(e) => setSearchQuery(e.target.value)}
                />
              </div>
              <Button>
                <RefreshCw className="h-4 w-4 mr-2" />
                Đồng bộ đơn hàng
              </Button>
            </div>
          </div>

          <Card>
            <CardHeader className="pb-2">
              <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center">
                <div>
                  <CardTitle>Danh sách đơn hàng</CardTitle>
                  <CardDescription>Quản lý tất cả đơn hàng từ các website</CardDescription>
                </div>
                <div className="flex space-x-2 mt-2 sm:mt-0">
                  <Select value={selectedStatus} onValueChange={setSelectedStatus}>
                    <SelectTrigger className="w-[180px]">
                      <SelectValue placeholder="Trạng thái" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="all">Tất cả trạng thái</SelectItem>
                      <SelectItem value="pending">Đang xử lý</SelectItem>
                      <SelectItem value="processing">Đang giao</SelectItem>
                      <SelectItem value="completed">Hoàn thành</SelectItem>
                      <SelectItem value="cancelled">Đã hủy</SelectItem>
                    </SelectContent>
                  </Select>
                  <Button variant="outline">
                    <Filter className="h-4 w-4 mr-2" />
                    Lọc
                  </Button>
                </div>
              </div>
            </CardHeader>
            <CardContent>
              <Tabs defaultValue="all" className="w-full">
                <TabsList className="mb-4">
                  <TabsTrigger value="all">Tất cả</TabsTrigger>
                  <TabsTrigger value="pending">Đang xử lý</TabsTrigger>
                  <TabsTrigger value="processing">Đang giao</TabsTrigger>
                  <TabsTrigger value="completed">Hoàn thành</TabsTrigger>
                  <TabsTrigger value="cancelled">Đã hủy</TabsTrigger>
                </TabsList>
                <TabsContent value="all">
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
                          <th scope="col" className="px-6 py-3">
                            Thao tác
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
                          <td className="px-6 py-4">
                            <div className="flex space-x-2">
                              <Button variant="ghost" size="icon">
                                <Eye className="h-4 w-4" />
                              </Button>
                              <Button variant="ghost" size="icon">
                                <Edit className="h-4 w-4" />
                              </Button>
                            </div>
                          </td>
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
                          <td className="px-6 py-4">
                            <div className="flex space-x-2">
                              <Button variant="ghost" size="icon">
                                <Eye className="h-4 w-4" />
                              </Button>
                              <Button variant="ghost" size="icon">
                                <Edit className="h-4 w-4" />
                              </Button>
                            </div>
                          </td>
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
                          <td className="px-6 py-4">
                            <div className="flex space-x-2">
                              <Button variant="ghost" size="icon">
                                <Eye className="h-4 w-4" />
                              </Button>
                              <Button variant="ghost" size="icon">
                                <Edit className="h-4 w-4" />
                              </Button>
                            </div>
                          </td>
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
                          <td className="px-6 py-4">
                            <div className="flex space-x-2">
                              <Button variant="ghost" size="icon">
                                <Eye className="h-4 w-4" />
                              </Button>
                              <Button variant="ghost" size="icon">
                                <Edit className="h-4 w-4" />
                              </Button>
                            </div>
                          </td>
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
                          <td className="px-6 py-4">
                            <div className="flex space-x-2">
                              <Button variant="ghost" size="icon">
                                <Eye className="h-4 w-4" />
                              </Button>
                              <Button variant="ghost" size="icon">
                                <Edit className="h-4 w-4" />
                              </Button>
                            </div>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </TabsContent>
                <TabsContent value="pending">
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
                          <th scope="col" className="px-6 py-3">
                            Thao tác
                          </th>
                        </tr>
                      </thead>
                      <tbody>
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
                          <td className="px-6 py-4">
                            <div className="flex space-x-2">
                              <Button variant="ghost" size="icon">
                                <Eye className="h-4 w-4" />
                              </Button>
                              <Button variant="ghost" size="icon">
                                <Edit className="h-4 w-4" />
                              </Button>
                            </div>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </TabsContent>
                {/* Các tab khác tương tự */}
              </Tabs>
            </CardContent>
            <CardFooter className="flex justify-between">
              <div className="text-sm text-gray-500 dark:text-gray-400">Hiển thị 1-5 của 25 đơn hàng</div>
              <div className="flex space-x-2">
                <Button variant="outline" size="sm">
                  Trước
                </Button>
                <Button variant="outline" size="sm">
                  Tiếp
                </Button>
              </div>
            </CardFooter>
          </Card>
        </main>
      </div>
    </div>
  )
}
