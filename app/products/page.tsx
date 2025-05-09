"use client"

import { useState } from "react"
import { Search, Filter, Eye, Edit, RefreshCw, Plus } from "lucide-react"
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { Badge } from "@/components/ui/badge"
import { Input } from "@/components/ui/input"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs"
import { Switch } from "@/components/ui/switch"
import { Sidebar } from "@/components/sidebar"
import { TopNav } from "@/components/top-nav"

export default function ProductsPage() {
  const [selectedSite, setSelectedSite] = useState("all")
  const [selectedCategory, setSelectedCategory] = useState("all")
  const [searchQuery, setSearchQuery] = useState("")

  return (
    <div className="flex min-h-screen bg-gray-50 dark:bg-gray-900">
      {/* Sidebar */}
      <Sidebar activePage="products" />

      {/* Main content */}
      <div className="flex-1 flex flex-col">
        {/* Top navigation */}
        <TopNav selectedSite={selectedSite} setSelectedSite={setSelectedSite} />

        {/* Page content */}
        <main className="flex-1 overflow-y-auto bg-gray-50 dark:bg-gray-900 p-4 sm:p-6 lg:p-8">
          <div className="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
            <h1 className="text-2xl font-semibold text-gray-900 dark:text-white mb-4 md:mb-0">Quản lý sản phẩm</h1>
            <div className="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2 w-full md:w-auto">
              <div className="relative w-full md:w-64">
                <Search className="absolute left-2.5 top-2.5 h-4 w-4 text-gray-500 dark:text-gray-400" />
                <Input
                  type="search"
                  placeholder="Tìm kiếm sản phẩm..."
                  className="pl-8"
                  value={searchQuery}
                  onChange={(e) => setSearchQuery(e.target.value)}
                />
              </div>
              <Button>
                <RefreshCw className="h-4 w-4 mr-2" />
                Đồng bộ sản phẩm
              </Button>
            </div>
          </div>

          <Card>
            <CardHeader className="pb-2">
              <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center">
                <div>
                  <CardTitle>Danh sách sản phẩm</CardTitle>
                  <CardDescription>Quản lý tất cả sản phẩm từ các website</CardDescription>
                </div>
                <div className="flex space-x-2 mt-2 sm:mt-0">
                  <Select value={selectedCategory} onValueChange={setSelectedCategory}>
                    <SelectTrigger className="w-[180px]">
                      <SelectValue placeholder="Danh mục" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="all">Tất cả danh mục</SelectItem>
                      <SelectItem value="clothing">Thời trang</SelectItem>
                      <SelectItem value="electronics">Điện tử</SelectItem>
                      <SelectItem value="cosmetics">Mỹ phẩm</SelectItem>
                    </SelectContent>
                  </Select>
                  <Button variant="outline">
                    <Filter className="h-4 w-4 mr-2" />
                    Lọc
                  </Button>
                  <Button>
                    <Plus className="h-4 w-4 mr-2" />
                    Thêm mới
                  </Button>
                </div>
              </div>
            </CardHeader>
            <CardContent>
              <Tabs defaultValue="all" className="w-full">
                <TabsList className="mb-4">
                  <TabsTrigger value="all">Tất cả</TabsTrigger>
                  <TabsTrigger value="instock">Còn hàng</TabsTrigger>
                  <TabsTrigger value="lowstock">Sắp hết</TabsTrigger>
                  <TabsTrigger value="outofstock">Hết hàng</TabsTrigger>
                </TabsList>
                <TabsContent value="all">
                  <div className="overflow-x-auto">
                    <table className="w-full text-sm text-left">
                      <thead className="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-800 dark:text-gray-400">
                        <tr>
                          <th scope="col" className="px-6 py-3">
                            Sản phẩm
                          </th>
                          <th scope="col" className="px-6 py-3">
                            SKU
                          </th>
                          <th scope="col" className="px-6 py-3">
                            Danh mục
                          </th>
                          <th scope="col" className="px-6 py-3">
                            Giá
                          </th>
                          <th scope="col" className="px-6 py-3">
                            Tồn kho
                          </th>
                          <th scope="col" className="px-6 py-3">
                            Website
                          </th>
                          <th scope="col" className="px-6 py-3">
                            Trạng thái
                          </th>
                          <th scope="col" className="px-6 py-3">
                            Thao tác
                          </th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr className="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                          <td className="px-6 py-4 font-medium">
                            <div className="flex items-center">
                              <div className="h-10 w-10 flex-shrink-0 mr-3">
                                <img
                                  className="h-10 w-10 rounded-md object-cover"
                                  src="/placeholder.svg?height=40&width=40"
                                  alt="Product"
                                />
                              </div>
                              <div>
                                <div className="font-medium">Áo thun nam basic</div>
                              </div>
                            </div>
                          </td>
                          <td className="px-6 py-4">SP-001</td>
                          <td className="px-6 py-4">Thời trang</td>
                          <td className="px-6 py-4">250.000đ</td>
                          <td className="px-6 py-4">
                            <Badge className="bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300">3</Badge>
                          </td>
                          <td className="px-6 py-4">Shop Thời Trang</td>
                          <td className="px-6 py-4">
                            <div className="flex items-center">
                              <Switch id="product-status-1" defaultChecked />
                            </div>
                          </td>
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
                          <td className="px-6 py-4 font-medium">
                            <div className="flex items-center">
                              <div className="h-10 w-10 flex-shrink-0 mr-3">
                                <img
                                  className="h-10 w-10 rounded-md object-cover"
                                  src="/placeholder.svg?height=40&width=40"
                                  alt="Product"
                                />
                              </div>
                              <div>
                                <div className="font-medium">Tai nghe bluetooth</div>
                              </div>
                            </div>
                          </td>
                          <td className="px-6 py-4">SP-245</td>
                          <td className="px-6 py-4">Điện tử</td>
                          <td className="px-6 py-4">750.000đ</td>
                          <td className="px-6 py-4">
                            <Badge className="bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300">2</Badge>
                          </td>
                          <td className="px-6 py-4">Shop Điện Tử</td>
                          <td className="px-6 py-4">
                            <div className="flex items-center">
                              <Switch id="product-status-2" defaultChecked />
                            </div>
                          </td>
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
                          <td className="px-6 py-4 font-medium">
                            <div className="flex items-center">
                              <div className="h-10 w-10 flex-shrink-0 mr-3">
                                <img
                                  className="h-10 w-10 rounded-md object-cover"
                                  src="/placeholder.svg?height=40&width=40"
                                  alt="Product"
                                />
                              </div>
                              <div>
                                <div className="font-medium">Kem dưỡng da</div>
                              </div>
                            </div>
                          </td>
                          <td className="px-6 py-4">SP-178</td>
                          <td className="px-6 py-4">Mỹ phẩm</td>
                          <td className="px-6 py-4">450.000đ</td>
                          <td className="px-6 py-4">
                            <Badge className="bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">
                              5
                            </Badge>
                          </td>
                          <td className="px-6 py-4">Shop Mỹ Phẩm</td>
                          <td className="px-6 py-4">
                            <div className="flex items-center">
                              <Switch id="product-status-3" defaultChecked />
                            </div>
                          </td>
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
                          <td className="px-6 py-4 font-medium">
                            <div className="flex items-center">
                              <div className="h-10 w-10 flex-shrink-0 mr-3">
                                <img
                                  className="h-10 w-10 rounded-md object-cover"
                                  src="/placeholder.svg?height=40&width=40"
                                  alt="Product"
                                />
                              </div>
                              <div>
                                <div className="font-medium">Quần jean nam</div>
                              </div>
                            </div>
                          </td>
                          <td className="px-6 py-4">SP-089</td>
                          <td className="px-6 py-4">Thời trang</td>
                          <td className="px-6 py-4">550.000đ</td>
                          <td className="px-6 py-4">
                            <Badge className="bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                              25
                            </Badge>
                          </td>
                          <td className="px-6 py-4">Shop Thời Trang</td>
                          <td className="px-6 py-4">
                            <div className="flex items-center">
                              <Switch id="product-status-4" defaultChecked />
                            </div>
                          </td>
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
                          <td className="px-6 py-4 font-medium">
                            <div className="flex items-center">
                              <div className="h-10 w-10 flex-shrink-0 mr-3">
                                <img
                                  className="h-10 w-10 rounded-md object-cover"
                                  src="/placeholder.svg?height=40&width=40"
                                  alt="Product"
                                />
                              </div>
                              <div>
                                <div className="font-medium">Loa bluetooth</div>
                              </div>
                            </div>
                          </td>
                          <td className="px-6 py-4">SP-156</td>
                          <td className="px-6 py-4">Điện tử</td>
                          <td className="px-6 py-4">1.250.000đ</td>
                          <td className="px-6 py-4">
                            <Badge className="bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                              12
                            </Badge>
                          </td>
                          <td className="px-6 py-4">Shop Điện Tử</td>
                          <td className="px-6 py-4">
                            <div className="flex items-center">
                              <Switch id="product-status-5" defaultChecked />
                            </div>
                          </td>
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
              <div className="text-sm text-gray-500 dark:text-gray-400">Hiển thị 1-5 của 42 sản phẩm</div>
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
