import type { Metadata } from "next"
import { getWebsites, getSyncLogs, getAutoSyncSettings } from "./actions"
import { SyncDashboard } from "./components/sync-dashboard"
import { SyncHistory } from "./components/sync-history"
import { AutoSyncSettings } from "./components/auto-sync-settings"
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"

export const metadata: Metadata = {
  title: "Đồng Bộ Dữ Liệu | WooCenter",
  description: "Đồng bộ dữ liệu từ các website WooCommerce",
}

export default async function SyncPage() {
  // Lấy danh sách website
  const websites = await getWebsites()

  // Lấy lịch sử đồng bộ
  const syncLogs = await getSyncLogs()

  // Lấy cài đặt đồng bộ tự động
  const autoSyncSettings = await getAutoSyncSettings()

  return (
    <div className="container mx-auto py-6 space-y-6">
      <div className="flex flex-col space-y-2">
        <h1 className="text-3xl font-bold tracking-tight">Đồng Bộ Dữ Liệu</h1>
        <p className="text-muted-foreground">Đồng bộ sản phẩm, đơn hàng và khách hàng từ các website WooCommerce.</p>
      </div>

      <Tabs defaultValue="dashboard" className="space-y-4">
        <TabsList>
          <TabsTrigger value="dashboard">Bảng Điều Khiển</TabsTrigger>
          <TabsTrigger value="history">Lịch Sử Đồng Bộ</TabsTrigger>
          <TabsTrigger value="settings">Cài Đặt Tự Động</TabsTrigger>
        </TabsList>

        <TabsContent value="dashboard" className="space-y-4">
          <Card>
            <CardHeader>
              <CardTitle>Đồng Bộ Dữ Liệu</CardTitle>
              <CardDescription>Chọn website và loại dữ liệu để bắt đầu đồng bộ.</CardDescription>
            </CardHeader>
            <CardContent>
              <SyncDashboard websites={websites} />
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>Đồng Bộ Gần Đây</CardTitle>
              <CardDescription>Các quá trình đồng bộ gần đây nhất.</CardDescription>
            </CardHeader>
            <CardContent>
              <SyncHistory syncLogs={syncLogs.slice(0, 5)} showWebsiteName={true} />
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="history" className="space-y-4">
          <Card>
            <CardHeader>
              <CardTitle>Lịch Sử Đồng Bộ</CardTitle>
              <CardDescription>Lịch sử đồng bộ dữ liệu từ tất cả các website.</CardDescription>
            </CardHeader>
            <CardContent>
              <SyncHistory syncLogs={syncLogs} showWebsiteName={true} />
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="settings" className="space-y-4">
          <Card>
            <CardHeader>
              <CardTitle>Cài Đặt Đồng Bộ Tự Động</CardTitle>
              <CardDescription>Cấu hình đồng bộ dữ liệu tự động theo lịch trình.</CardDescription>
            </CardHeader>
            <CardContent>
              <AutoSyncSettings websites={websites} initialSettings={autoSyncSettings} />
            </CardContent>
          </Card>
        </TabsContent>
      </Tabs>
    </div>
  )
}
