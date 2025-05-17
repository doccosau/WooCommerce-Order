"use client"

import { useState, useEffect } from "react"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { Button } from "@/components/ui/button"
import { DatePickerWithRange } from "@/components/ui/date-range-picker"
import { Download, Filter, RefreshCw } from "lucide-react"
import { Sidebar } from "@/components/layout/sidebar"
import { TopNav } from "@/components/layout/top-nav"
import { RevenueDetailChart } from "./components/revenue-detail-chart"
import { OrdersDetailChart } from "./components/orders-detail-chart"
import { ProductsDetailChart } from "./components/products-detail-chart"
import { CustomersDetailChart } from "./components/customers-detail-chart"
import { RevenueTable } from "./components/revenue-table"
import { OrdersTable } from "./components/orders-table"
import { ProductsTable } from "./components/products-table"
import { CustomersTable } from "./components/customers-table"
import { Sheet, SheetContent, SheetDescription, SheetHeader, SheetTitle, SheetTrigger } from "@/components/ui/sheet"
import { Checkbox } from "@/components/ui/checkbox"
import { Label } from "@/components/ui/label"
import { Separator } from "@/components/ui/separator"
import { addDays } from "date-fns"
import { vi } from "date-fns/locale"
import type { DateRange } from "react-day-picker"

export default function DetailedReportsPage() {
  const [selectedSite, setSelectedSite] = useState("all")
  const [selectedPeriod, setSelectedPeriod] = useState("month")
  const [isLoading, setIsLoading] = useState(false)
  const [date, setDate] = useState<DateRange | undefined>({
    from: addDays(new Date(), -30),
    to: new Date(),
  })

  // Giả lập tải dữ liệu khi thay đổi bộ lọc
  useEffect(() => {
    setIsLoading(true)
    const timer = setTimeout(() => {
      setIsLoading(false)
    }, 1000)
    return () => clearTimeout(timer)
  }, [selectedSite, selectedPeriod, date])

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
            <h1 className="text-2xl font-semibold text-gray-900 dark:text-white mb-4 md:mb-0">Báo Cáo Chi Tiết</h1>
            <div className="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2 w-full md:w-auto">
              <DatePickerWithRange date={date} setDate={setDate} locale={vi} />

              <Select value={selectedPeriod} onValueChange={setSelectedPeriod}>
                <SelectTrigger className="w-[180px]">
                  <SelectValue placeholder="Chọn thời gian" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="day">Hôm nay</SelectItem>
                  <SelectItem value="week">Tuần này</SelectItem>
                  <SelectItem value="month">Tháng này</SelectItem>
                  <SelectItem value="quarter">Quý này</SelectItem>
                  <SelectItem value="year">Năm nay</SelectItem>
                </SelectContent>
              </Select>

              <Sheet>
                <SheetTrigger asChild>
                  <Button variant="outline">
                    <Filter className="h-4 w-4 mr-2" />
                    Bộ lọc
                  </Button>
                </SheetTrigger>
                <SheetContent>
                  <SheetHeader>
                    <SheetTitle>Bộ lọc báo cáo</SheetTitle>
                    <SheetDescription>Tùy chỉnh bộ lọc để xem báo cáo chi tiết</SheetDescription>
                  </SheetHeader>
                  <div className="py-4">
                    <h3 className="mb-2 font-medium">Danh mục sản phẩm</h3>
                    <div className="space-y-2">
                      <div className="flex items-center space-x-2">
                        <Checkbox id="category-1" />
                        <Label htmlFor="category-1">Thời trang nam</Label>
                      </div>
                      <div className="flex items-center space-x-2">
                        <Checkbox id="category-2" />
                        <Label htmlFor="category-2">Thời trang nữ</Label>
                      </div>
                      <div className="flex items-center space-x-2">
                        <Checkbox id="category-3" />
                        <Label htmlFor="category-3">Điện thoại</Label>
                      </div>
                      <div className="flex items-center space-x-2">
                        <Checkbox id="category-4" />
                        <Label htmlFor="category-4">Phụ kiện điện tử</Label>
                      </div>
                      <div className="flex items-center space-x-2">
                        <Checkbox id="category-5" />
                        <Label htmlFor="category-5">Mỹ phẩm</Label>
                      </div>
                    </div>

                    <Separator className="my-4" />

                    <h3 className="mb-2 font-medium">Trạng thái đơn hàng</h3>
                    <div className="space-y-2">
                      <div className="flex items-center space-x-2">
                        <Checkbox id="status-1" />
                        <Label htmlFor="status-1">Đang xử lý</Label>
                      </div>
                      <div className="flex items-center space-x-2">
                        <Checkbox id="status-2" />
                        <Label htmlFor="status-2">Hoàn thành</Label>
                      </div>
                      <div className="flex items-center space-x-2">
                        <Checkbox id="status-3" />
                        <Label htmlFor="status-3">Đã hủy</Label>
                      </div>
                      <div className="flex items-center space-x-2">
                        <Checkbox id="status-4" />
                        <Label htmlFor="status-4">Đang giao hàng</Label>
                      </div>
                    </div>

                    <Separator className="my-4" />

                    <h3 className="mb-2 font-medium">Phương thức thanh toán</h3>
                    <div className="space-y-2">
                      <div className="flex items-center space-x-2">
                        <Checkbox id="payment-1" />
                        <Label htmlFor="payment-1">Thanh toán khi nhận hàng (COD)</Label>
                      </div>
                      <div className="flex items-center space-x-2">
                        <Checkbox id="payment-2" />
                        <Label htmlFor="payment-2">Chuyển khoản ngân hàng</Label>
                      </div>
                      <div className="flex items-center space-x-2">
                        <Checkbox id="payment-3" />
                        <Label htmlFor="payment-3">Thẻ tín dụng/ghi nợ</Label>
                      </div>
                      <div className="flex items-center space-x-2">
                        <Checkbox id="payment-4" />
                        <Label htmlFor="payment-4">Ví điện tử</Label>
                      </div>
                    </div>

                    <div className="mt-6 flex space-x-2">
                      <Button className="flex-1">Áp dụng</Button>
                      <Button variant="outline" className="flex-1">
                        Đặt lại
                      </Button>
                    </div>
                  </div>
                </SheetContent>
              </Sheet>

              <Button variant="outline">
                <RefreshCw className="h-4 w-4 mr-2" />
                Làm mới
              </Button>

              <Button>
                <Download className="h-4 w-4 mr-2" />
                Xuất báo cáo
              </Button>
            </div>
          </div>

          <Tabs defaultValue="revenue" className="w-full">
            <TabsList className="mb-4">
              <TabsTrigger value="revenue">Doanh thu</TabsTrigger>
              <TabsTrigger value="orders">Đơn hàng</TabsTrigger>
              <TabsTrigger value="products">Sản phẩm</TabsTrigger>
              <TabsTrigger value="customers">Khách hàng</TabsTrigger>
            </TabsList>

            <TabsContent value="revenue">
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
                    <CardDescription>Doanh thu trung bình/ngày</CardDescription>
                  </CardHeader>
                  <CardContent>
                    <div className="text-2xl font-bold">5.081.000đ</div>
                    <p className="text-xs text-muted-foreground">
                      <span className="text-green-500">+8.2%</span> so với kỳ trước
                    </p>
                  </CardContent>
                </Card>
                <Card>
                  <CardHeader className="pb-2">
                    <CardDescription>Giá trị đơn hàng trung bình</CardDescription>
                  </CardHeader>
                  <CardContent>
                    <div className="text-2xl font-bold">622.163đ</div>
                    <p className="text-xs text-muted-foreground">
                      <span className="text-red-500">-2.3%</span> so với kỳ trước
                    </p>
                  </CardContent>
                </Card>
                <Card>
                  <CardHeader className="pb-2">
                    <CardDescription>Tỷ lệ hoàn thành đơn hàng</CardDescription>
                  </CardHeader>
                  <CardContent>
                    <div className="text-2xl font-bold">95.1%</div>
                    <p className="text-xs text-muted-foreground">
                      <span className="text-green-500">+1.8%</span> so với kỳ trước
                    </p>
                  </CardContent>
                </Card>
              </div>

              <Card className="mb-6">
                <CardHeader>
                  <CardTitle>Biểu đồ doanh thu chi tiết</CardTitle>
                  <CardDescription>Doanh thu theo ngày trong kỳ</CardDescription>
                </CardHeader>
                <CardContent>
                  <RevenueDetailChart className="h-[400px]" isLoading={isLoading} />
                </CardContent>
              </Card>

              <Card>
                <CardHeader>
                  <CardTitle>Phân tích doanh thu chi tiết</CardTitle>
                  <CardDescription>Chi tiết doanh thu theo danh mục sản phẩm và website</CardDescription>
                </CardHeader>
                <CardContent>
                  <RevenueTable isLoading={isLoading} />
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
                    <CardDescription>Đơn hàng trung bình/ngày</CardDescription>
                  </CardHeader>
                  <CardContent>
                    <div className="text-2xl font-bold">8.2</div>
                    <p className="text-xs text-muted-foreground">
                      <span className="text-green-500">+15.5%</span> so với kỳ trước
                    </p>
                  </CardContent>
                </Card>
                <Card>
                  <CardHeader className="pb-2">
                    <CardDescription>Tỷ lệ đơn hàng mới</CardDescription>
                  </CardHeader>
                  <CardContent>
                    <div className="text-2xl font-bold">68.3%</div>
                    <p className="text-xs text-muted-foreground">
                      <span className="text-green-500">+5.3%</span> so với kỳ trước
                    </p>
                  </CardContent>
                </Card>
                <Card>
                  <CardHeader className="pb-2">
                    <CardDescription>Tỷ lệ hủy đơn hàng</CardDescription>
                  </CardHeader>
                  <CardContent>
                    <div className="text-2xl font-bold">4.9%</div>
                    <p className="text-xs text-muted-foreground">
                      <span className="text-red-500">+0.8%</span> so với kỳ trước
                    </p>
                  </CardContent>
                </Card>
              </div>

              <Card className="mb-6">
                <CardHeader>
                  <CardTitle>Biểu đồ đơn hàng chi tiết</CardTitle>
                  <CardDescription>Số lượng đơn hàng theo ngày và trạng thái</CardDescription>
                </CardHeader>
                <CardContent>
                  <OrdersDetailChart className="h-[400px]" isLoading={isLoading} />
                </CardContent>
              </Card>

              <Card>
                <CardHeader>
                  <CardTitle>Phân tích đơn hàng chi tiết</CardTitle>
                  <CardDescription>Chi tiết đơn hàng theo trạng thái và phương thức thanh toán</CardDescription>
                </CardHeader>
                <CardContent>
                  <OrdersTable isLoading={isLoading} />
                </CardContent>
              </Card>
            </TabsContent>

            <TabsContent value="products">
              <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <Card>
                  <CardHeader className="pb-2">
                    <CardDescription>Tổng sản phẩm đã bán</CardDescription>
                  </CardHeader>
                  <CardContent>
                    <div className="text-2xl font-bold">583</div>
                    <p className="text-xs text-muted-foreground">
                      <span className="text-green-500">+14.2%</span> so với kỳ trước
                    </p>
                  </CardContent>
                </Card>
                <Card>
                  <CardHeader className="pb-2">
                    <CardDescription>Sản phẩm bán chạy nhất</CardDescription>
                  </CardHeader>
                  <CardContent>
                    <div className="text-xl font-bold truncate">iPhone 13 Pro Max</div>
                    <p className="text-xs text-muted-foreground">Đã bán 28 sản phẩm</p>
                  </CardContent>
                </Card>
                <Card>
                  <CardHeader className="pb-2">
                    <CardDescription>Danh mục bán chạy nhất</CardDescription>
                  </CardHeader>
                  <CardContent>
                    <div className="text-xl font-bold">Điện thoại</div>
                    <p className="text-xs text-muted-foreground">Chiếm 32.5% doanh thu</p>
                  </CardContent>
                </Card>
                <Card>
                  <CardHeader className="pb-2">
                    <CardDescription>Tỷ lệ sản phẩm hết hàng</CardDescription>
                  </CardHeader>
                  <CardContent>
                    <div className="text-2xl font-bold">8.3%</div>
                    <p className="text-xs text-muted-foreground">
                      <span className="text-red-500">+2.1%</span> so với kỳ trước
                    </p>
                  </CardContent>
                </Card>
              </div>

              <Card className="mb-6">
                <CardHeader>
                  <CardTitle>Biểu đồ sản phẩm chi tiết</CardTitle>
                  <CardDescription>Phân tích sản phẩm theo danh mục và doanh số</CardDescription>
                </CardHeader>
                <CardContent>
                  <ProductsDetailChart className="h-[400px]" isLoading={isLoading} />
                </CardContent>
              </Card>

              <Card>
                <CardHeader>
                  <CardTitle>Phân tích sản phẩm chi tiết</CardTitle>
                  <CardDescription>Chi tiết sản phẩm theo doanh số và số lượng bán</CardDescription>
                </CardHeader>
                <CardContent>
                  <ProductsTable isLoading={isLoading} />
                </CardContent>
              </Card>
            </TabsContent>

            <TabsContent value="customers">
              <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <Card>
                  <CardHeader className="pb-2">
                    <CardDescription>Tổng khách hàng</CardDescription>
                  </CardHeader>
                  <CardContent>
                    <div className="text-2xl font-bold">1.245</div>
                    <p className="text-xs text-muted-foreground">
                      <span className="text-green-500">+8.2%</span> so với kỳ trước
                    </p>
                  </CardContent>
                </Card>
                <Card>
                  <CardHeader className="pb-2">
                    <CardDescription>Khách hàng mới</CardDescription>
                  </CardHeader>
                  <CardContent>
                    <div className="text-2xl font-bold">132</div>
                    <p className="text-xs text-muted-foreground">
                      <span className="text-green-500">+15.5%</span> so với kỳ trước
                    </p>
                  </CardContent>
                </Card>
                <Card>
                  <CardHeader className="pb-2">
                    <CardDescription>Tỷ lệ khách hàng quay lại</CardDescription>
                  </CardHeader>
                  <CardContent>
                    <div className="text-2xl font-bold">42.3%</div>
                    <p className="text-xs text-muted-foreground">
                      <span className="text-green-500">+3.7%</span> so với kỳ trước
                    </p>
                  </CardContent>
                </Card>
                <Card>
                  <CardHeader className="pb-2">
                    <CardDescription>Chi tiêu trung bình/khách hàng</CardDescription>
                  </CardHeader>
                  <CardContent>
                    <div className="text-2xl font-bold">1.250.000đ</div>
                    <p className="text-xs text-muted-foreground">
                      <span className="text-green-500">+5.8%</span> so với kỳ trước
                    </p>
                  </CardContent>
                </Card>
              </div>

              <Card className="mb-6">
                <CardHeader>
                  <CardTitle>Biểu đồ khách hàng chi tiết</CardTitle>
                  <CardDescription>Phân tích khách hàng theo thời gian và nguồn</CardDescription>
                </CardHeader>
                <CardContent>
                  <CustomersDetailChart className="h-[400px]" isLoading={isLoading} />
                </CardContent>
              </Card>

              <Card>
                <CardHeader>
                  <CardTitle>Phân tích khách hàng chi tiết</CardTitle>
                  <CardDescription>Chi tiết khách hàng theo chi tiêu và số lượng đơn hàng</CardDescription>
                </CardHeader>
                <CardContent>
                  <CustomersTable isLoading={isLoading} />
                </CardContent>
              </Card>
            </TabsContent>
          </Tabs>
        </main>
      </div>
    </div>
  )
}
